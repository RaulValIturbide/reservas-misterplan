
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

/* DML insertar los datos ejemplo*/

INSERT INTO reserva (nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas,fecha_creacion)
VALUES
    ('María García', 'maria.garcia@ejemplo.com', 'Hotel Pilar Zaragoza',
     '2026-11-10', '2026-11-14', 'confirmada', 380.00, 'Llegada tarde, sobre las 22:00',
     '2026-10-01 10:15:00'
    ),
    ('Diego Fernández', 'diego.fernandez@ejemplo.com', 'Hostal Bahía Verde',
     '2026-11-20', '2026-11-23', 'pendiente', 195.00, NULL,
     '2026-10-10 18:30:00'
    ),
    ('Lucía Martínez', 'lucia.martinez@ejemplo.com', 'Apartamentos Centro Histórico',
     '2026-12-05', '2026-12-08', 'confirmada', 270.00, 'Viaja con un perro pequeño',
     '2026-10-05 12:00:00'
    ),
    ('Juan Gil', 'juan.gil@ejemplo.com', 'Casa Rural Valle de Ansó',
     '2026-12-23', '2026-12-27', 'pendiente', 520.00, 'Solicita cuna para bebé',
     '2026-10-12 20:10:00'
    ),
    ('Sandra Ruiz', 'sandra.ruiz@ejemplo.com', 'Hotel Mudéjar Teruel',
     '2026-10-03', '2026-10-05', 'cancelada', 210.00, 'Prefiere habitación en planta alta',
     '2026-09-15 11:00:00'
    ),
    ('Begoña Ibáñez', 'begona.ibanez@ejemplo.com', 'Hotel Mirador del Pirineo',
     '2026-09-12', '2026-09-15', 'confirmada', 345.00, NULL,
     '2026-08-20 09:30:00'
    ),
    ('Andrés Muñoz', 'andres.munoz@ejemplo.com', 'Albergue Camino de Santiago',
     '2026-10-18', '2026-10-19', 'confirmada', 25.00, 'Peregrino, llega a pie',
     '2026-10-08 07:45:00'
    ),
    ('Sofía García', 'sofia.garcia@ejemplo.com', 'Hotel Pilar Zaragoza',
     '2027-01-15', '2027-01-18', 'pendiente', 285.00, NULL,
     '2026-10-14 13:00:00'
    ),
    ('Pau Serra', 'pau.serra@ejemplo.com', 'Apartamento Playa Salou',
     '2026-08-01', '2026-08-08', 'confirmada', 840.00, NULL,
     '2026-06-15 17:00:00'
    ),
    ('Carmen López', 'carmen.lopez@ejemplo.com', 'Hostal Bahía Verde',
     '2026-11-02', '2026-11-04', 'cancelada', 130.00, NULL,
     '2026-10-02 14:00:00'
    ),
    ('Iñigo Iturbide', 'inigo.iturbide@ejemplo.com', 'Casa Rural Valle de Ansó',
     '2027-02-13', '2027-02-15', 'pendiente', 260.00, 'Escapada de San Valentín',
     '2026-10-13 22:05:00'
    ),
    ('Ana Peña', 'ana.pena@ejemplo.com', 'Hotel Mirador del Pirineo',
     '2027-01-30', '2027-02-02', 'confirmada', 405.00, 'Necesita plaza de parking',
     '2026-10-07 11:40:00'
    ),
    ('Federico Rojo', 'federico.rojo@ejemplo.com', 'Apartamentos Centro Histórico',
     '2026-12-28', '2027-01-02', 'confirmada', 650.00, 'Celebra Nochevieja en la ciudad',
     '2026-09-30 19:00:00'
    ),
    ('Rocío Navarro', 'rocio.navarro@ejemplo.com', 'Apartamentos Centro Histórico',
     '2026-10-24', '2026-10-26', 'cancelada', 180.00, NULL,
     '2026-09-25 10:00:00'
    ),
    ('Marcos Gil', 'marcos.gil@ejemplo.com', 'Hotel Mudéjar Teruel',
     '2026-11-27', '2026-11-29', 'pendiente', 198.00, NULL,
     '2026-10-14 16:45:00'
    );

INSERT INTO reserva_evento (reserva_id, tipo, descripcion, fecha_creacion)
VALUES
    (1, 'creada', 'Reserva creada', '2026-10-01 10:15:00'),
    (1, 'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-10-02 09:00:00'),
    (2, 'creada', 'Reserva creada', '2026-10-10 18:30:00'),
    (3, 'creada', 'Reserva creada', '2026-10-05 12:00:00'),
    (3, 'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-10-06 08:45:00'),
    (4, 'creada', 'Reserva creada', '2026-10-12 20:10:00'),
    (5, 'creada', 'Reserva creada', '2026-09-15 11:00:00'),
    (5, 'cancelada', 'Cancelada por el huésped: cambio de fechas del vuelo', '2026-09-28 16:20:00'),
    (6, 'creada', 'Reserva creada', '2026-08-20 09:30:00'),
    (6, 'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-08-21 10:00:00'),
    (7, 'creada', 'Reserva creada', '2026-10-08 07:45:00'),
    (7, 'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-10-08 08:30:00'),
    (8, 'creada', 'Reserva creada', '2026-10-14 13:00:00'),
    (9, 'creada', 'Reserva creada', '2026-06-15 17:00:00'),
    (9, 'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-06-16 09:15:00'),
    (10, 'creada', 'Reserva creada', '2026-10-02 14:00:00'),
    (10, 'cancelada', 'Cancelada por el huésped', '2026-10-09 10:30:00'),
    (11, 'creada', 'Reserva creada', '2026-10-13 22:05:00'),
    (12, 'creada', 'Reserva creada', '2026-10-07 11:40:00'),
    (12, 'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-10-07 12:10:00'),
    (13, 'creada', 'Reserva creada', '2026-09-30 19:00:00'),
    (13, 'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-10-01 09:20:00'),
    (14, 'creada', 'Reserva creada', '2026-09-25 10:00:00'),
    (14, 'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-09-26 09:00:00'),
    (14, 'cancelada', 'Cancelada por el alojamiento: overbooking', '2026-10-11 09:00:00'),
    (15, 'creada', 'Reserva creada', '2026-10-14 16:45:00');
