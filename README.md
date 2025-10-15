# 🎓 Sistema Inteligente de Recomendaciones Académicas (SIRA)

## 📋 Descripción
SIRA es una plataforma web innovadora que personaliza la experiencia de aprendizaje mediante inteligencia artificial, analizando el rendimiento académico y patrones de estudio para recomendar recursos educativos adaptados a cada estudiante.

## 🚀 Stack Tecnológico

### Stack Principal
- **Laravel 12.x** con Starter Kit
- **Vue.js 3** con Inertia.js
- **MySQL 8.0**
- **Redis** para cache y queues
- **Laravel Sail** para desarrollo con Docker

### Servicio de IA (Próximamente)
- **Python 3.11** con FastAPI
- **scikit-learn** para modelos de recomendación
- **pandas & numpy** para procesamiento de datos

## 📦 Instalación y Configuración

### Prerrequisitos
- Windows 10/11 con WSL2
- Docker Desktop
- Git

### Instalación del Proyecto

1. **Clonar el repositorio existente**
```bash
git clone https://github.com/AlinaEsther/Proyecto-Final-SIRA.git
cd Proyecto-Final-SIRA
```

2. **Instalar dependencias con Composer**
```bash
composer install
```

3. **Configurar archivo .env**
```bash
cp .env.example .env
```

4. **Instalar Laravel Sail**
```bash
php artisan sail:install
# Seleccionar: mysql, redis
```

5. **Iniciar los contenedores con Sail**
```bash
./vendor/bin/sail up -d
```

6. **Configuración inicial de la aplicación**
```bash
# Generar application key
./vendor/bin/sail artisan key:generate

# Ejecutar migraciones
./vendor/bin/sail artisan migrate

# Seeders iniciales (si existen)
./vendor/bin/sail artisan db:seed

# Limpiar cachés
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear

# Optimizar la aplicación
./vendor/bin/sail artisan optimize
```

7. **Instalar dependencias de NPM (si no se inician automáticamente) y iniciar el entorno de desarrollo**
```bash
npm install
npm run dev
```

8. **Acceder a la aplicación**
- Aplicación: http://localhost:8000
- phpMyAdmin: http://localhost:8080 (si está configurado)

9. **Alias para Sail (Recomendado en Windows)**
```bash
Agregar al archivo ~/.bashrc o ~/.zshrc: 
- nano ~/.bashrc
- sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
Guarda (Ctrl+O) y sal (Ctrl+X)
- source ~/.bashrc
Luego puedes usar solo Sail [comando] en vez de llamarlo desde ./vendor/bin/sail
```


## 🔧 Comandos Útiles

```bash
# Construir contenedores sin cache
docker compose build --no-cache

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
