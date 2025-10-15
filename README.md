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
git clone https://github.com/AlinaEsther/Proyecto-Final-SIRA.git sira
cd sira

# ⚠️ IMPORTANTE: Cambiar a la rama develop (rama de desarrollo activa)
git checkout develop
```

2. **Desplegar el proyecto con contenedor temporal para instalar dependencias con Composer**
```bash
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php84-composer:latest \
  composer install  --ignore-platform-reqs
```

3. **Configurar archivo .env**
```bash
cp .env.example .env
```

4. **Iniciar los contenedores con Sail (WSL2) o Docker (Windows con Docker Desktop)**
```bash
./vendor/bin/sail up -d
docker compose up -d
```

5. **Configuración inicial de la aplicación**
```bash
# Generar application key
./vendor/bin/sail artisan key:generate

# Ejecutar migraciones (si hay)
./vendor/bin/sail artisan migrate

# Seeders iniciales (si existen)
./vendor/bin/sail artisan db:seed
```

6. **Instalar dependencias de NPM (si no se inician automáticamente) y reiniciar el contenedor al ejecutarse**
```bash
npm install
```

7. **Acceder a la aplicación**
- Aplicación: http://localhost:8000
- phpMyAdmin: http://localhost:8080

8. **Alias para Sail (Recomendado en WSL2)**
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
# Limpiar cachés
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear

# Optimizar la aplicación
./vendor/bin/sail artisan optimize

# Construir contenedores sin cache
docker compose build --no-cache

# Compilar asset del frontend
npm run build

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
