# Docker Setup - Morpheus

## 🐳 Iniciar Bases de Datos

### Opción 1: Ambas bases de datos (MySQL + PostgreSQL)

```bash
docker-compose up -d
```

### Opción 2: Solo MySQL

```bash
docker-compose up -d mysql
```

### Opción 3: Solo PostgreSQL

```bash
docker-compose up -d postgres
```

## 📊 Verificar Estado

```bash
docker-compose ps
```

## 🔌 Conexiones

### MySQL
- **Host**: localhost
- **Port**: 3306
- **User**: root
- **Password**: rootpassword
- **Database**: test

**DSN PHP**:
```php
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
```

### PostgreSQL
- **Host**: localhost
- **Port**: 5432
- **User**: postgres
- **Password**: postgres
- **Database**: test

**DSN PHP**:
```php
$pdo = new PDO('pgsql:host=localhost;dbname=test', 'postgres', 'postgres');
```

## 🛠️ Comandos Útiles

### Ver logs
```bash
docker-compose logs -f
```

### Detener contenedores
```bash
docker-compose down
```

### Detener y eliminar volúmenes (⚠️ borra datos)
```bash
docker-compose down -v
```

### Reiniciar servicios
```bash
docker-compose restart
```

### Conectar a MySQL CLI
```bash
docker exec -it morpheus-mysql mysql -uroot -prootpassword test
```

### Conectar a PostgreSQL CLI
```bash
docker exec -it morpheus-postgres psql -U postgres -d test
```

## 📦 Requisitos PHP

Asegúrate de tener las extensiones PDO instaladas:

```bash
# Verificar extensiones
php -m | grep pdo
```

Deberías ver:
- pdo_mysql
- pdo_pgsql

Si falta `pdo_pgsql`, instálalo según tu sistema:

**Windows (XAMPP/WAMP)**:
- Editar `php.ini`
- Descomentar: `extension=pdo_pgsql`
- Reiniciar servidor

**Linux (Ubuntu/Debian)**:
```bash
sudo apt-get install php-pgsql
sudo systemctl restart apache2
```

**macOS (Homebrew)**:
```bash
brew install php
# pdo_pgsql viene incluido
```

## 🧪 Verificar Conexión

```php
<?php
// Test MySQL
try {
    $mysql = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
    echo "✅ MySQL conectado\n";
} catch (PDOException $e) {
    echo "❌ MySQL error: " . $e->getMessage() . "\n";
}

// Test PostgreSQL
try {
    $pgsql = new PDO('pgsql:host=localhost;dbname=test', 'postgres', 'postgres');
    echo "✅ PostgreSQL conectado\n";
} catch (PDOException $e) {
    echo "❌ PostgreSQL error: " . $e->getMessage() . "\n";
}
```

## 🔄 Migrar Datos

### Exportar desde MySQL
```bash
docker exec morpheus-mysql mysqldump -uroot -prootpassword test > backup.sql
```

### Importar a MySQL
```bash
docker exec -i morpheus-mysql mysql -uroot -prootpassword test < backup.sql
```

### Exportar desde PostgreSQL
```bash
docker exec morpheus-postgres pg_dump -U postgres test > backup.sql
```

### Importar a PostgreSQL
```bash
docker exec -i morpheus-postgres psql -U postgres test < backup.sql
```

## 🚀 Quick Start

1. **Iniciar contenedores**:
   ```bash
   docker-compose up -d
   ```

2. **Verificar que están corriendo**:
   ```bash
   docker-compose ps
   ```

3. **Ejecutar setup scripts**:
   ```bash
   # MySQL
   docker exec -i morpheus-mysql mysql -uroot -prootpassword test < examples/setup.sql
   
   # PostgreSQL
   docker exec -i morpheus-postgres psql -U postgres test < examples/setup_postgres.sql
   ```

4. **Ejecutar tests**:
   ```bash
   vendor/bin/phpunit
   ```

## 🛑 Troubleshooting

### Puerto ya en uso
Si el puerto 3306 o 5432 ya está en uso, edita `docker-compose.yml`:

```yaml
ports:
  - "3307:3306"  # MySQL en puerto 3307
  - "5433:5432"  # PostgreSQL en puerto 5433
```

### Contenedor no inicia
```bash
docker-compose logs mysql
docker-compose logs postgres
```

### Resetear todo
```bash
docker-compose down -v
docker-compose up -d
```

---

**Nota**: Los datos se persisten en volúmenes Docker. Para eliminarlos completamente usa `docker-compose down -v`.
