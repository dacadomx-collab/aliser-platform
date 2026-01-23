# ALISER Platform - Objetivos del Proyecto

## Visión General

ALISER es una plataforma digital integral diseñada para una tienda de autoservicio en Baja California Sur (BCS). Transforma la presencia digital de ALISER mediante una plataforma tecnológica de vanguardia (Web + CMS + IA Chatbot) que centralice la operación informativa, comercial y de expansión de la marca en Baja California Sur.

El proyecto se rige por una **arquitectura API-First**, garantizando que el sitio web y el Chatbot consuman la misma "Fuente Única de Verdad". El objetivo es crear un ecosistema digital sólido, escalable y optimizado para Inteligencia Artificial (AIO) que mejore la experiencia del cliente y optimice las operaciones del negocio.

---

## Los 4 Pilares Fundamentales

### 1. Informativo

**Objetivo**: Proporcionar información clara, accesible y actualizada sobre la tienda, sus servicios, ubicación y operaciones. Centralizar la identidad de la marca y su historia, valores de calidad y servicios.

**Funcionalidades clave**:
- Información de la tienda y sucursales (horarios, ubicación, contacto)
- Historia, valores de calidad y servicios de ALISER
- Catálogo de productos disponible
- Información sobre servicios adicionales
- Noticias y actualizaciones del negocio
- Guías y recursos útiles para los clientes
- Optimización SEO para visibilidad local
- Geolocalización avanzada de sucursales

**Tecnologías y consideraciones**:
- Diseño responsive (mobile-first)
- Integración avanzada con la API de Google Maps para mostrar sucursales con horarios y datos de contacto en tiempo real
- Marcado Schema.org (JSON-LD) para que motores de respuesta e IAs (AIO) identifiquen a ALISER como el líder local
- Contenido optimizado para búsquedas locales

---

### 2. Bolsa de Trabajo

**Objetivo**: Sistema de gestión de recursos humanos que facilite la contratación, el seguimiento de candidatos y la gestión del talento. Permite al área de RH publicar vacantes y gestionar candidatos de forma eficiente.

**Funcionalidades clave**:
- Publicación de vacantes disponibles con perfiles detallados, imágenes (flyers) y fechas límite
- Portal para que los candidatos envíen sus solicitudes mediante formularios dinámicos
- Sistema de gestión de currículums (CVs)
- Captura de CVs y solicitud de entrevistas automatizadas
- Proceso de selección y seguimiento de candidatos
- Panel administrativo para que el equipo de ALISER gestione los candidatos
- Notificaciones y comunicación con candidatos
- Filtrado y búsqueda de perfiles

**Tecnologías y consideraciones**:
- Base de datos para almacenar candidatos y vacantes
- Sistema de autenticación y autorización
- Upload y gestión de archivos (CVs y flyers)
- Formularios dinámicos para captura de información
- Panel de administración intuitivo
- Protección de datos personales (LFPDPPP)

---

### 3. Adquisición de Terrenos

**Objetivo**: Facilitar la adquisición de nuevos puntos de venta mediante la participación ciudadana. Plataforma para gestionar solicitudes y propuestas relacionadas con la adquisición de terrenos para expansión del negocio.

**Funcionalidades clave**:
- Sección dedicada con flyers de búsqueda de terrenos y especificaciones técnicas requeridas
- Formulario de contacto directo para propietarios que desean vender terrenos
- Portal para recibir propuestas de terrenos disponibles
- Conexión directa del dueño del terreno con el departamento de expansión de ALISER
- Gestión de información de propiedades (ubicación, tamaño, precio, características)
- Sistema de evaluación y seguimiento de propuestas
- Documentación y documentos legales
- Comunicación con propietarios y agentes
- Integración con mapas para visualización de ubicaciones

**Tecnologías y consideraciones**:
- Formularios seguros y validados
- Gestión de documentos (PDFs, imágenes, flyers)
- Integración con Google Maps API
- Sistema de notificaciones
- Panel administrativo para gestión y seguimiento
- Protección de información sensible

---

### 4. Promociones

**Objetivo**: Sistema dinámico para gestionar y mostrar promociones, ofertas especiales, cupones, descuentos y ofertas quincenales a los clientes. Centro de promociones y fidelización con gestión de vigencias y características especiales por sucursal.

**Funcionalidades clave**:
- Catálogo de promociones activas (cupones, descuentos y ofertas quincenales)
- Gestión de ofertas (crear, editar, desactivar) con carga de flyers y fechas de vigencia
- Categorización de promociones por tipo de producto
- Gestión de vigencias (fecha inicio/fin) y características especiales por sucursal
- Visualización destacada en el sitio web
- Omnicanalidad: Las promociones creadas en el CMS se notifican instantáneamente al Chatbot de WhatsApp y se actualizan en el sitio web
- Sistema de notificaciones para clientes (email, SMS opcional)
- Historial de promociones
- Estadísticas y análisis de efectividad

**Tecnologías y consideraciones**:
- Base de datos para promociones
- Sistema de imágenes para productos en oferta (flyers)
- Filtrado y búsqueda de promociones
- Gestión de cupones y códigos de descuento
- Panel administrativo para gestión de ofertas
- Integración con Chatbot de WhatsApp para notificaciones
- Integración con sistema de puntos o membresía (futuro)

---

## Principios de Diseño y Desarrollo

### Separación de Preocupaciones (SoC)
- **HTML**: Estructura y semántica
- **CSS**: Presentación y diseño visual
- **JavaScript**: Comportamiento e interactividad

### Mobile-First
- Diseño optimizado para dispositivos móviles (mobile-first extremo)
- Experiencia táctil fluida
- Navegación optimizada para el pulgar ("Thumb-Friendly")
- Transiciones y animaciones suaves

### Identidad Visual
- **Verde Oscuro** (`#256737`): Color principal, representa confianza y naturaleza
- **Arena** (`#ECD4A8`): Color secundario, evoca la playa y ambiente costero de BCS
- **Verde Azulado** (`#439184`): Color terciario, complementa la paleta con frescura

### Optimización para IA (AIO)
- Estructura de datos clara y semántica
- Schema.org markup para mejor comprensión por parte de IAs
- Metadatos descriptivos
- Código limpio y bien documentado

---

## Stack Tecnológico Propuesto

### Arquitectura
- **API-First**: Arquitectura que garantiza que el sitio web y el Chatbot consuman la misma "Fuente Única de Verdad"
- Separación de preocupaciones (SoC): Código modularizado y refactorizado

### Frontend
- HTML5, CSS3 (con variables CSS)
- JavaScript (Vanilla o framework moderno)
- Diseño responsive (mobile-first extremo)
- Optimización de imágenes: Formatos WebP/AVIF y Lazy Loading
- Velocidad objetivo: Carga menor a 1.5s

### Backend
- API RESTful (arquitectura API-First)
- Base de datos (PostgreSQL, MongoDB, MySQL)
- Autenticación JWT
- Gestión de archivos (CVs, flyers, documentos)
- CMS para gestión de contenido

### Integraciones
- Chatbot de WhatsApp con IA
- Google Maps API (geolocalización avanzada)
- Sistema de notificaciones

### Infraestructura
- Versionado con Git
- Variables de entorno (.env)
- Documentación clara

---

## Especificaciones Técnicas de los Endpoints (Backend)

### Módulo de Autenticación & Seguridad
- `POST /api/v1/auth/login`: Acceso para administradores/editores
- `GET /api/v1/auth/me`: Validación de roles y permisos

### Módulo de Promociones (Pilar 4)
- `GET /api/v1/promos`: Lista pública de ofertas (Web y Chatbot)
- `POST /api/v1/promos`: Creación de cupones con carga de flyers y fechas de vigencia

### Módulo de Vacantes (Pilar 2)
- `GET /api/v1/jobs`: Lista de plazas disponibles
- `POST /api/v1/jobs/apply`: Recepción de solicitudes de candidatos

### Módulo de Expansión (Pilar 3)
- `POST /api/v1/land-proposals`: Envío de datos de terrenos por parte del público

---

## Estándares de Calidad Profesional

### Código
- Refactorización total, modularizado y con separación de preocupaciones (SoC)
- Código limpio y bien documentado

### Diseño
- Mobile-First extremo, optimizado para uso con el pulgar ("Thumb-Friendly")
- Experiencia de usuario fluida y accesible

### Velocidad
- Carga menor a 1.5s mediante formatos WebP/AVIF y Lazy Loading
- Optimización continua de rendimiento

---

## Próximos Pasos

1. ✅ Estructura base del proyecto
2. ⏳ Diseño de la arquitectura del backend
3. ⏳ Desarrollo de módulos por pilares
4. ⏳ Implementación de seguridad
5. ⏳ Testing y optimización
6. ⏳ Despliegue y monitoreo

---

**Última actualización**: 2026
**Versión del documento**: 1.1
