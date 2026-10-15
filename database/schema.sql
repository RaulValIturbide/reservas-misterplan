
/*La base de datos creada con el sistema utf8mb4 que es el más compleot de MySQL*/
CREATE DATABASE IF NOT EXISTS reservas_misterplan CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE reservas_misterplan;
/*lo limpiamos para que quede tal y como lo queremos*/
DROP TABLE IF EXISTS reserva_evento;
DROP TABLE IF EXISTS reserva;


/*DDL creación de las tablas*/
CREATE TABLE reserva(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_huesped VARCHAR(150) NOT NULL ,
    email_huesped VARCHAR(300) NOT NULL ,
    nombre_alojamiento VARCHAR(300) NOT NULL ,
    fecha_entrada DATE NOT NULL ,
    fecha_salida DATE NOT NULL ,
    estado ENUM('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
    importe DECIMAL(10,2) NOT NULL ,
    notas TEXT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE reserva_evento(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT UNSIGNED NOT NULL ,
    tipo VARCHAR(50) NOT NULL,
    descripcion VARCHAR(500),
    fecha_creacion DATETIME NOT NULL  DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reserva_id FOREIGN KEY(reserva_id) REFERENCES reserva(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

/* TODO DML insertar los datos ejemplo*/

