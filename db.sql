CREATE DATABASE IF NOT EXISTS curso_angular;
USE curso_angular;

CREATE TABLE IF NOT EXISTS productos (
  id INT(255) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(255),
  descripcion TEXT,
  precio VARCHAR(255),
  imagen VARCHAR(255),
  CONSTRAINT pk_producto PRIMARY KEY(id)
) ENGINE = InnoDB;
