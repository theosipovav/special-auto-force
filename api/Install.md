
# Первичная настройка

### Проверить текущего пользователя веб‑сервера
#### Через Apache runtime‑конфигурацию
```bash
apache2ctl -S
```

Вывод:
```text
VirtualHost configuration:
*:80                   localhost (/etc/apache2/sites-enabled/000-default.conf:1)
ServerRoot: "/etc/apache2"
Main DocumentRoot: "/var/www/html"
Main ErrorLog: "/var/log/apache2/error.log"
Mutex mpm-accept: using_defaults
Mutex watchdog-callback: using_defaults
Mutex rewrite-map: using_defaults
Mutex default: dir="/var/run/apache2/" mechanism=default
PidFile: "/var/run/apache2/apache2.pid"
Define: DUMP_VHOSTS
Define: DUMP_RUN_CFG
User: name="www-data" id=33
Group: name="www-data" id=33
```


#### Через переменные окружения Apache
```bash
cat /etc/apache2/envvars | grep -E "APACHE_RUN_USER|APACHE_RUN_GROUP"
```

Вывод:
```text
: ${APACHE_RUN_USER:=www-data}
export APACHE_RUN_USER
: ${APACHE_RUN_GROUP:=www-data}
export APACHE_RUN_GROUP
```

Результат:
- Пользователь: www-data
- Группа: www-data
- ID: 33


#### Проверка прав
```bash
ls -l runtime
# total 8
# drwxrwxr-x 4 1000 1000 4096 Aug 20 00:11 cache
# drwxrwxr-x 2 1000 1000 4096 Aug 20 00:11 logs

ls -ld runtime/cache runtime/logs
# drwxrwxr-x 4 1000 1000 4096 Aug 20 00:11 runtime/cache
# drwxrwxr-x 2 1000 1000 4096 Aug 20 00:11 runtime/logs

ls -l web/
# total 4
# drwxrwxrwx 2 1000 1000 4096 Aug 20 00:11 assets

ls -ld web web/assets
# drwxr-xr-x 3 1000 1000 4096 Aug 23 16:48 web
# drwxrwxrwx 2 1000 1000 4096 Aug 20 00:11 web/assets
```


## Настройка прав для логирования ошибок в Yii2
> заранее перейдем в папку с проектом yii2

```bash
mkdir -p runtime/logs runtime/cache
chown www-data:www-data runtime runtime/cache runtime/logs
```


## Настройка прав для weboot
> заранее перейдем в папку с проектом yii2
```bash
mkdir -p web/assets web/uploads/images
chown www-data:www-data web web/assets web/uploads web/uploads/images
```


## Создание миграции
```bash
# Через консоль сервера 
php yii migrate/create fill_table_parameter
# Через docker
docker exec -it reg-ru-php php ./api/yii migrate/create init
docker exec -it reg-ru-php php ./api/yii migrate/create fill_tables
docker exec -it reg-ru-php php ./api/yii migrate/create fill_table_parameter
```

## Применение миграции
```bash
# Через консоль сервера 
php yii migrate
# Через docker
docker exec -it reg-ru-php php ./api/yii migrate
```

## Откатить последнюю применённую миграцию
```bash
# Через консоль сервера 
php yii migrate/down 1
# Через docker
docker exec -it reg-ru-php php ./api/yii migrate/down 1
```