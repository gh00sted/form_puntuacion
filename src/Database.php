<?php

class Database
{
    private $pdo;
    private $host;
    private $db_name;
    private $user;
    private $password;
    private $port;

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
        $this->port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 5432;
        $this->db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'puntuacion_db';
        $this->user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'postgres';
        $this->password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? 'postgres_password';

        $this->connect();
    }

    private function connect()
    {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            $this->pdo = new PDO($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die('Error de conexión: ' . $e->getMessage());
        }
    }

    public function insertExpediente($data)
    {
        try {
            $query = "INSERT INTO expedientes (
                        id_expediente, nombre_completo, puntuacion,
                        llego_tiempo, informo_aseguradora, fotos_antes,
                        localizo_averia, llamo_encargado, foto_durante,
                        reparo_1_visita, llamo_encargado_en_visita, justificado, foto_despues, need_seg_gremio,
                        gremio_correcto, tomo_datos_perj, tomo_medidas_est_pav,
                        nps, firma_asegurado, cerro_exp
                      ) VALUES (
                        :id_expediente, :nombre_completo, :puntuacion,
                        :llego_tiempo, :informo_aseguradora, :fotos_antes,
                        :localizo_averia, :llamo_encargado, :foto_durante,
                        :reparo_1_visita, :llamo_encargado_en_visita, :justificado, :foto_despues, :need_seg_gremio,
                        :gremio_correcto, :tomo_datos_perj, :tomo_medidas_est_pav,
                        :nps, :firma_asegurado, :cerro_exp
                      )";
            
            $stmt = $this->pdo->prepare($query);
            
            // Bind de manera explícita con tipos
            $stmt->bindValue(':id_expediente', $data['id_expediente'], PDO::PARAM_STR);
            $stmt->bindValue(':nombre_completo', $data['nombre_completo'], PDO::PARAM_STR);
            $stmt->bindValue(':puntuacion', $data['puntuacion'], PDO::PARAM_STR);
            
            // Para booleanos, convertir null a NULL de SQL
            $stmt->bindValue(':llego_tiempo', $data['llego_tiempo'], $data['llego_tiempo'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':informo_aseguradora', $data['informo_aseguradora'], $data['informo_aseguradora'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':fotos_antes', $data['fotos_antes'], $data['fotos_antes'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':localizo_averia', $data['localizo_averia'], $data['localizo_averia'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':llamo_encargado', $data['llamo_encargado'], $data['llamo_encargado'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':foto_durante', $data['foto_durante'], $data['foto_durante'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':reparo_1_visita', $data['reparo_1_visita'], $data['reparo_1_visita'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':llamo_encargado_en_visita', $data['llamo_encargado_en_visita'], $data['llamo_encargado_en_visita'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':justificado', $data['justificado'], $data['justificado'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':foto_despues', $data['foto_despues'], $data['foto_despues'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':need_seg_gremio', $data['need_seg_gremio'], $data['need_seg_gremio'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':gremio_correcto', $data['gremio_correcto'], $data['gremio_correcto'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':tomo_datos_perj', $data['tomo_datos_perj'], $data['tomo_datos_perj'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':tomo_medidas_est_pav', $data['tomo_medidas_est_pav'], $data['tomo_medidas_est_pav'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':firma_asegurado', $data['firma_asegurado'], $data['firma_asegurado'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            $stmt->bindValue(':cerro_exp', $data['cerro_exp'], $data['cerro_exp'] !== null ? PDO::PARAM_BOOL : PDO::PARAM_NULL);
            
            $stmt->bindValue(':nps', $data['nps'] ?? null, PDO::PARAM_STR);
            
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'duplicate key') !== false) {
                throw new Exception('El ID de expediente ya existe');
            }
            throw new Exception('Error al guardar: ' . $e->getMessage());
        }
    }

    public function getExpedientes()
    {
        try {
            $query = "SELECT * FROM expedientes ORDER BY fecha_creacion DESC";
            
            $stmt = $this->pdo->query($query);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception('Error al obtener expedientes: ' . $e->getMessage());
        }
    }

    public function getAllExpedientes()
    {
        try {
            $query = "SELECT id, id_expediente, nombre_completo, puntuacion, fecha_creacion 
                      FROM expedientes 
                      ORDER BY fecha_creacion DESC";
            
            $stmt = $this->pdo->query($query);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception('Error al obtener expedientes: ' . $e->getMessage());
        }
    }
}
