# 📌 Gestor de Tareas

Aplicación web para la gestión de tareas personales desarrollada como Trabajo de Fin de Grado (TFG) del ciclo de **Desarrollo de Aplicaciones Web (DAW)**.

## 🚀 Tecnologías utilizadas

| Tecnología               |                Uso             |
|--------------------------|--------------------------------|
| PHP 8.x                  |     Backend y lógica de negocio|
| MySQL                    |      Base de datos relacional  |
| HTML5, CSS3, Bootstrap 5 | Diseño responsive              |
| JavaScript               | Interactividad y filtros       |
| Composer                 | Gestor de dependencias         |
| PHPMailer                | Envío de correos electrónicos  |
| TCPDF                    | Generación de PDFs             |
| Dotenv                   | Variables de entorno           |

## 📋 Requisitos previos

Antes de instalar el proyecto, asegúrate de tener:

- [XAMPP](https://www.apachefriends.org/es/index.html) (Apache + MySQL + PHP)
- [Composer](https://getcomposer.org/download/)
- [Git](https://git-scm.com/) (opcional, para clonar el repositorio)

## 🔧 Instalación paso a paso

### 1. Clonar o descargar el proyecto

```bash
git clone https://github.com/josehm7/tfg_tareas.git
```
## Crea una base de datos en mysql e importa la crea cion de tablas desde el archivo sql desde la carpeta sql

## Despues de crear , rellena las constantes de .env

## Necesitas composer intalado:  ejecuta en el directorio raiz  <code>composer install<code> 
## Se descargaran e instalaran las librerias necesarias 
 

## 👥 Roles de usuario

| Rol | Permisos |
|-----|----------|
| **Usuario** | Ver, crear, editar y eliminar sus propias tareas |
| **Administrador** | Ver todas las tareas de todos los usuarios, gestionar usuarios (cambiar roles, eliminar usuarios) |

### Credenciales de administrador (por defecto)

| Email              | Contraseña |
|--------------------|------------|
| admin@gmail.com    | 789102     |