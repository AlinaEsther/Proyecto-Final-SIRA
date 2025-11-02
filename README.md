# 🎓 Sistema Inteligente de Recomendaciones Académicas (SIRA)

## 📋 Descripción
SIRA es una plataforma web innovadora que personaliza la experiencia de aprendizaje mediante inteligencia artificial, analizando el rendimiento académico y patrones de estudio para recomendar recursos educativos adaptados a cada estudiante.

## 🚀 Stack Tecnológico
### Stack Principal
- **Laravel 12.x** con Starter Kit
- **Vue 3** con Composition API y TypeScript - Framework JavaScript para el frontend
- **Inertia.js** - Para crear SPAs sin necesidad de construir una API
- **Tailwind CSS** - Framework de utilidades CSS
- **shadcn-vue** - Librería de componentes UI
- **SQlite3**
- **MySQL 8.0**
- **Redis** para cache y queues

### Servicio de IA (Próximamente)
- **Python 3.11** con FastAPI
- **scikit-learn** para modelos de recomendación
- **pandas & numpy** para procesamiento de datos

## 📦 Requisitos del Sistema

Antes de comenzar, asegúrate de tener instalado:

- **PHP** >= 8.2
- **Composer** >= 2.7
- **Node.js** >= 18.x
- **NPM** o **Yarn**
- **SQLite** (incluido por defecto en PHP)

### Instalación del Proyecto

1. **Clonar el repositorio existente**
```bash
git clone https://github.com/AlinaEsther/Proyecto-Final-SIRA.git sira
cd sira
```

2. **Instalar dependencias con Composer**
```bash
composer install
```

3. **Configurar archivo .env**
```bash
copy .env.example .env
```

4. **Generar Clave de Aplicación**
```bash
php artisan key:generate
```

5. **Instalar dependencias de node para el stack de desarrollo**
```bash
npm install
```

6. **Correr las migraciones**
```bash
php artisan migrate
```

7. **Iniciar la aplicación**
```bash
composer run dev
```

- Otra opción:
```bash
php artisan serve
```
```bash
npm run dev
```


8. **Acceder a la aplicación**
- Aplicación: http://localhost:8000

## 🔧 Comandos Útiles

```bash
# Poblar la base de datos con datos de prueba:
php artisan db:seed

# Ejecuta las migraciones y seeders juntos:
php artisan migrate:fresh --seed

# Limpiar caché de configuración
php artisan config:clear

# Limpiar caché de rutas
php artisan route:clear

# Construir contenedores sin cache
php artisan optimize

# Ver logs
tail logs -f

# Ejecutar Tinker
php artisan tinker

# Ejecutar pruebas
php artisan test
```



<!-- 
## Servicio Python ML (Configuración futura)

Agregar al docker-compose.yml cuando esté listo:

ml_service:
  build:
    context: ./ml-service
    dockerfile: Dockerfile
  container_name: sira_ml
  environment:
    - DATABASE_URL=mysql://sail:password@mysql:3306/sira
    - REDIS_URL=redis://redis:6379
  volumes:
    - ./ml-service:/app
  ports:
    - "8001:8001"
  networks:
    - sail
  command: uvicorn main:app --host 0.0.0.0 --port 8001 --reload
-->
## 📄 Licencia

Este proyecto es desarrollado como trabajo final para la asignatura TDS Virtual bajo la supervisión del Prof. Willis Ezequiel Polanco Caraballo.
