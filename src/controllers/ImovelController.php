// src/controllers/ImovelController.php
class ImovelController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance('clientes');
    }
    
    public function listar() {
        $stmt = $this->db->query("SELECT * FROM imoveis ORDER BY destaque DESC, data_cadastro DESC");
        return $stmt->fetchAll();
    }
    
    public function buscar($id) {
        $stmt = $this->db->prepare("SELECT * FROM imoveis WHERE id = ?");
        $stmt->execute([$id]);
        $imovel = $stmt->fetch();
        
        if ($imovel) {
            $stmt = $this->db->prepare("SELECT * FROM imagens_imoveis WHERE imovel_id = ? ORDER BY ordem");
            $stmt->execute([$id]);
            $imovel['imagens'] = $stmt->fetchAll();
        }
        
        return $imovel;
    }
    
    public function criar($dados, $imagens) {
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO imoveis 
                (titulo, descricao, tipo, preco, area, quartos, banheiros, endereco, cidade, estado, cep, latitude, longitude, status, destaque)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $dados['titulo'],
                $dados['descricao'],
                $dados['tipo'],
                $dados['preco'],
                $dados['area'],
                $dados['quartos'],
                $dados['banheiros'],
                $dados['endereco'],
                $dados['cidade'],
                $dados['estado'],
                $dados['cep'],
                $dados['latitude'],
                $dados['longitude'],
                $dados['status'],
                $dados['destaque'] ?? 0
            ]);
            
            $imovelId = $this->db->lastInsertId();
            
            // Processar imagens
            $this->salvarImagens($imovelId, $imagens);
            
            $this->db->commit();
            return $imovelId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao criar imóvel: " . $e->getMessage());
            return false;
        }
    }
    
    private function salvarImagens($imovelId, $imagens) {
        $uploadDir = '/assets/images/imoveis/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        
        foreach ($imagens['tmp_name'] as $index => $tmpName) {
            if ($imagens['error'][$index] !== UPLOAD_ERR_OK) continue;
            
            $fileType = mime_content_type($tmpName);
            if (!in_array($fileType, $allowedTypes)) continue;
            
            $ext = pathinfo($imagens['name'][$index], PATHINFO_EXTENSION);
            $filename = uniqid('img_') . '.' . $ext;
            $destPath = $_SERVER['DOCUMENT_ROOT'] . $uploadDir . $filename;
            
            if (move_uploaded_file($tmpName, $destPath)) {
                $stmt = $this->db->prepare("
                    INSERT INTO imagens_imoveis (imovel_id, caminho, ordem)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $imovelId,
                    $uploadDir . $filename,
                    $index
                ]);
            }
        }
    }
    
    public function atualizar($id, $dados, $imagens = null, $imagensRemover = []) {
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare("
                UPDATE imoveis SET
                titulo = ?,
                descricao = ?,
                tipo = ?,
                preco = ?,
                area = ?,
                quartos = ?,
                banheiros = ?,
                endereco = ?,
                cidade = ?,
                estado = ?,
                cep = ?,
                latitude = ?,
                longitude = ?,
                status = ?,
                destaque = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $dados['titulo'],
                $dados['descricao'],
                $dados['tipo'],
                $dados['preco'],
                $dados['area'],
                $dados['quartos'],
                $dados['banheiros'],
                $dados['endereco'],
                $dados['cidade'],
                $dados['estado'],
                $dados['cep'],
                $dados['latitude'],
                $dados['longitude'],
                $dados['status'],
                $dados['destaque'] ?? 0,
                $id
            ]);
            
            // Remover imagens selecionadas
            if (!empty($imagensRemover)) {
                $placeholders = implode(',', array_fill(0, count($imagensRemover), '?'));
                $stmt = $this->db->prepare("SELECT caminho FROM imagens_imoveis WHERE id IN ($placeholders)");
                $stmt->execute($imagensRemover);
                $imagensParaExcluir = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $stmt = $this->db->prepare("DELETE FROM imagens_imoveis WHERE id IN ($placeholders)");
                $stmt->execute($imagensRemover);
                
                // Excluir arquivos físicos
                foreach ($imagensParaExcluir as $caminho) {
                    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $caminho;
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }
            
            // Adicionar novas imagens
            if ($imagens && !empty($imagens['tmp_name'][0])) {
                $this->salvarImagens($id, $imagens);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao atualizar imóvel: " . $e->getMessage());
            return false;
        }
    }
    
    public function excluir($id) {
        $this->db->beginTransaction();
        
        try {
            // Obter caminhos das imagens para excluir
            $stmt = $this->db->prepare("SELECT caminho FROM imagens_imoveis WHERE imovel_id = ?");
            $stmt->execute([$id]);
            $imagens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Excluir imóvel (cascata exclui imagens)
            $stmt = $this->db->prepare("DELETE FROM imoveis WHERE id = ?");
            $stmt->execute([$id]);
            
            // Excluir arquivos físicos
            foreach ($imagens as $caminho) {
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $caminho;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao excluir imóvel: " . $e->getMessage());
            return false;
        }
    }
    
    public function filtrar($filtros) {
        $where = [];
        $params = [];
        
        if (!empty($filtros['tipo'])) {
            $where[] = "tipo = ?";
            $params[] = $filtros['tipo'];
        }
        
        if (!empty($filtros['cidade'])) {
            $where[] = "cidade LIKE ?";
            $params[] = '%' . $filtros['cidade'] . '%';
        }
        
        if (!empty($filtros['preco_min'])) {
            $where[] = "preco >= ?";
            $params[] = $filtros['preco_min'];
        }
        
        if (!empty($filtros['preco_max'])) {
            $where[] = "preco <= ?";
            $params[] = $filtros['preco_max'];
        }
        
        if (!empty($filtros['quartos'])) {
            $where[] = "quartos >= ?";
            $params[] = $filtros['quartos'];
        }
        
        if (!empty($filtros['status'])) {
            $where[] = "status = ?";
            $params[] = $filtros['status'];
        }
        
        $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
        $orderClause = "ORDER BY " . ($filtros['ordenar'] ?? 'destaque DESC, data_cadastro DESC');
        
        $sql = "SELECT * FROM imoveis $whereClause $orderClause";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
}
