-- Crear tabla de expedientes
CREATE TABLE IF NOT EXISTS expedientes (
    id SERIAL PRIMARY KEY,
    id_expediente VARCHAR(50) NOT NULL UNIQUE,
    nombre_completo VARCHAR(255) NOT NULL,
    puntuacion NUMERIC(5, 2) NOT NULL,
    llego_tiempo BOOLEAN,
    informo_aseguradora BOOLEAN,
    fotos_antes BOOLEAN,
    localizo_averia BOOLEAN,
    llamo_encargado BOOLEAN,
    foto_durante BOOLEAN,
    reparo_1_visita BOOLEAN,
    llamo_encargado_en_visita BOOLEAN,
    justificado BOOLEAN,
    foto_despues BOOLEAN,
    need_seg_gremio BOOLEAN,
    gremio_correcto BOOLEAN,
    tomo_datos_perj BOOLEAN,
    tomo_medidas_est_pav BOOLEAN,
    nps VARCHAR(20),
    firma_asegurado BOOLEAN,
    cerro_exp BOOLEAN,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear índice para búsquedas rápidas
CREATE INDEX idx_id_expediente ON expedientes(id_expediente);
