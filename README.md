# Transbank Opencart 3.x Webpay Plugin

## Descripción

Este plugin de Opencart 3.x implementa 
el [SDK PHP de Transbank](https://github.com/TransbankDevelopers/transbank-sdk-php) 

## Dependencias

* transbank/transbank-sdk (composer)
* fpdf


## Instalación del plugin para un comercio

El manual de instalación para el usuario final se encuentra disponible [acá](docs/INSTALLATION.md) o 
en PDF [acá](https://github.com/TransbankDevelopers/transbank-plugin-opencart-webpay/blob/master/docs/INSTALLATION.pdf)


## Nota  
- La versión del sdk de php se encuentra en el archivo `config.sh` y 
en `src/upload/system/library/transbank/composer.json`

**NOTA:** La versión del sdk de php se encuentra en el script config.sh

## Preparar el proyecto para bajar dependencias

    ./config.sh

## Crear una versión del plugin empaquetado 

    ./package.sh

## Desarrollo

Para apoyar el levantamiento rápido de un ambiente de desarrollo, hemos creado la especificación de contenedores a 
través de Docker Compose.

Para usarlo seguir el siguiente [README Opencart 3.x](./docker-opencart3)

### Actualizar el PDF de instalación
Instalar `markdown-pdf` con `npm i -g markdown-pdf` y luego para generar el archivo `INSTALLATION.pdf` sea debe ejecutar: 
```
cd docs/
markdown-pdf INSTALLATION.md
```

## Generar una nueva versión

Para generar una nueva versión, se debe crear un PR (con un título "Prepare release X.Y.Z" con los valores que correspondan para `X`, `Y` y `Z`). Se debe seguir el estándar semver para determinar si se incrementa el valor de `X` (si hay cambios no retrocompatibles), `Y` (para mejoras retrocompatibles) o `Z` (si sólo hubo correcciones a bugs).

En ese PR deben incluirse los siguientes cambios:

1. Modificar el archivo `CHANGELOG.md` para incluir una nueva entrada (al comienzo) para `X.Y.Z` que explique en español los cambios.

Luego de obtener aprobación del pull request, debes mezclar a master e inmediatamente generar un release en GitHub con el tag `vX.Y.Z`. En la descripción del release debes poner lo mismo que agregaste al changelog.

Con eso Travis CI generará automáticamente una nueva versión del plugin y actualizará el Release de Github con el zip del plugin.
