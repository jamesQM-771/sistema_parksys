USE sistema_parksys;

CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  token VARCHAR(80) NOT NULL UNIQUE,
  expira_en DATETIME NOT NULL,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_password_resets_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS auditoria (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  accion VARCHAR(120) NOT NULL,
  detalle TEXT NULL,
  ip VARCHAR(60) NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_usuario_fecha (usuario_id, creado_en),
  CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO configuracion (clave, valor)
VALUES ('modo_cobro', 'POR_MINUTO')
ON DUPLICATE KEY UPDATE valor = valor;

INSERT INTO configuracion (clave, valor)
VALUES ('minutos_gracia', '5')
ON DUPLICATE KEY UPDATE valor = valor;

INSERT INTO configuracion (clave, valor)
VALUES ('logo_url', 'assets/img/logo-default.png')
ON DUPLICATE KEY UPDATE valor = CASE
  WHEN valor IS NULL OR TRIM(valor) = '' THEN VALUES(valor)
  ELSE valor
END;