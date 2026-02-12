DB_STRUCTURE.md (Source of Truth)

Base de Datos: aliser_db  Cotejamiento: utf8mb4_general_ci

🏗️ Tabla: terrenos
#,Columna,Tipo,Nulo,Predeterminado,Notas
1,id,int(11),NO,,"Primary Key, AI"
2,tipo_oferta,"enum('renta', 'venta')",NO,,
3,nombre_completo,varchar(255),NO,,
4,email,varchar(150),NO,,
5,telefono,varchar(20),NO,,
6,ubicacion_maps,text,NO,,Link Google Maps
7,metros_cuadrados,"decimal(10,2)",NO,,
8,expectativa_economica,"decimal(15,2)",NO,,
9,situacion_legal,text,SÍ,NULL,
10,descripcion_adicional,text,SÍ,NULL,
11,imagen_terreno,varchar(255),SÍ,NULL,Ruta del archivo
12 | estatus | enum('nuevo','en_revision','aprobado','rechazado') | SÍ | 'nuevo' | Control de flujo
13,creado_en,timestamp,NO,CURRENT_TIMESTAMP,

🏗️ Tabla: usuarios_admin
#,Columna,Tipo,Nulo,Predeterminado,Notas
1,id,int(11),NO,,"PK, Auto Increment"
2,usuario,varchar(50),NO,,Login ID
3,password,varchar(255),NO,,Hash BCRYPT
4,nombre,varchar(100),NO,,Nombre Real
5,rol,"enum('admin','editor')",NO,'admin',Nivel de acceso
6,ultimo_login,datetime,SÍ,NULL,Trazabilidad

🏗️ Tabla: vacantes
#,Columna,Tipo,Nulo,Predeterminado,Notas
1,id,int(11),NO,,"PK, Auto Increment"
2,titulo,varchar(150),NO,,Ejemplo: Cajero
3,sucursal,varchar(100),NO,,"La Paz, Los Cabos, etc."
4,descripcion,text,NO,,Requisitos y funciones
5,imagen_flyer,varchar(255),SÍ,NULL,Ruta a assets/img/vacantes/
6,estatus,"enum('activa','pausada')",NO,'activa',Control de visibilidad
7,fecha_creacion,timestamp,NO,CURRENT_TIMESTAMP,