-- PostgreSQL database dump

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';
SET default_table_access_method = heap;

-- Crear tabla operarios
CREATE TABLE IF NOT EXISTS public.operarios (
    id SERIAL PRIMARY KEY,
    nombre_completo VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT true NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla checklist_items
CREATE TABLE IF NOT EXISTS public.checklist_items (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    texto TEXT NOT NULL,
    puntos_si NUMERIC(5,2) DEFAULT 0 NOT NULL,
    orden INTEGER NOT NULL
);

-- Crear tabla expedientes
CREATE TABLE IF NOT EXISTS public.expedientes (
    id SERIAL PRIMARY KEY,
    id_expediente VARCHAR(50) NOT NULL UNIQUE,
    operario_id INTEGER NOT NULL REFERENCES public.operarios(id) ON DELETE RESTRICT,
    puntuacion NUMERIC(5,2) NOT NULL,
    fecha_expediente DATE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla checklist_respuestas
CREATE TABLE IF NOT EXISTS public.checklist_respuestas (
    expediente_id INTEGER NOT NULL REFERENCES public.expedientes(id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES public.checklist_items(id),
    marcado BOOLEAN DEFAULT false NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW() NOT NULL,
    PRIMARY KEY (expediente_id, item_id)
);

-- Crear índices
CREATE INDEX IF NOT EXISTS idx_id_expediente ON public.expedientes(id_expediente);
CREATE INDEX IF NOT EXISTS idx_checklist_respuestas_expediente ON public.checklist_respuestas(expediente_id);

-- Insertar datos en checklist_items
INSERT INTO public.checklist_items (codigo, texto, puntos_si, orden) VALUES
('LLEGO_A_TIEMPO',        'Llegó a tiempo',                              1.00,  1),
('INFORMO_ASEG_TRAM',    'Informó a aseguradora y tramitadora',          0.50,  2),
('FOTOS_ANTES',          'Fotos antes',                                 0.50,  3),
('LOCALIZO_AVERIA',      'Localizó avería',                             1.00,  4),
('FOTO_DURANTE',         'Foto durante',                                0.50,  5),
('REPARO_PRIMERA',       'Reparó en 1ª visita',                         1.00,  6),
('LLAMO_ENCARGADO',      'Llamó a encargado (No localizó avería)',       0.50,  7),
('JUSTIFICADO',          'Justificado',                                 0.50,  8),
('FOTO_DESPUES',         'Foto después',                                0.50,  9),
('SEGUNDO_GREMIO',       'Segundo gremio',                              0.33, 10),
('TOMO_DATOS',           'Tomó datos del perjudicado',                  0.33, 11),
('TOMO_MEDIDAS',         'Tomó medidas, estancias y pavimento',         0.33, 12),
('FIRMA_ASEGURADO',      'Firma asegurado',                             0.25, 13),
('EXPEDIENTE_CERRADO',   'Se ha cerrado expediente',                    0.25, 14),
('LLAMO_ENCARGADO_2',    'Llamó a encargado (No reparó en 1ª)',          0.50, 15)
ON CONFLICT (codigo) DO NOTHING;
