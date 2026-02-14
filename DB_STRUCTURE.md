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
1,id,int(11) unsigned,NO,,"PK, Auto Increment"
2,nombre_completo,varchar(100),NO,,Nombre completo del usuario
3,usuario,varchar(50),NO,,Login ID
4,password,varchar(255),NO,,Hash BCRYPT
5,email,varchar(100),NO,,Correo del usuario
6,whatsapp,varchar(20),SÍ,NULL,Contacto WhatsApp
7,rol,text,NO,,Roles CSV (ej: TALENTO,MARCA)
8,activo,tinyint(1),NO,1,1=Activo,0=Inactivo
9,ultimo_acceso,datetime,SÍ,NULL,Trazabilidad
10,creado_en,timestamp,NO,CURRENT_TIMESTAMP,
11,actualizado_en,timestamp,NO,CURRENT_TIMESTAMP,on update CURRENT_TIMESTAMP

🏗️ Tabla: vacantes
#,Columna,Tipo,Nulo,Predeterminado,Notas
1,id,int(11),NO,,"PK, AI"
2,titulo,varchar(200),NO,,
3,sucursal,varchar(100),NO,'Matriz', Ubicación de la vacante
4,descripcion,text,NO,,
5,imagen_flyer,varchar(255),SÍ,NULL,Ruta del archivo
6,fecha_inicio,date,SÍ,NULL,
7,fecha_fin,date,SÍ,NULL,
8,activo,tinyint(1),SÍ,1,1=Activo, 0=Inactivo
9,creado_en,timestamp,NO,CURRENT_TIMESTAMP,
10,actualizado_en,timestamp,NO,CURRENT_TIMESTAMP,on update CURRENT_TIMESTAMP
11,estatus,"enum('activa','pausada')",NO,activa

🏗️ Tabla: promociones
#,Columna,Tipo,Nulo,Predeterminado,Notas
1,id,int(11),NO,,"PK, AI"
2,tipo_publico,"enum('menudeo','mayoreo')",NO,'menudeo',
3,titulo,varchar(200),NO,,
4,descripcion,text,NO,,
5,imagen_flyer,varchar(255),SÍ,NULL,Ruta del archivo
6,fecha_inicio,date,NO,,
7,fecha_fin,date,NO,,
8,estatus,"enum('activa','pausada')",NO,activa,
9,creado_en,timestamp,NO,CURRENT_TIMESTAMP,
10,actualizado_en,timestamp,NO,CURRENT_TIMESTAMP,on update
