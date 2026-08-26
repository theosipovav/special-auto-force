



### Просмотр логов
#### С консоли сервера 
```bash
tail -f ./api/runtime/logs/app.log
```

#### Через docker
```bash
docker exec -it reg-ru-php tail -f ./api/runtime/logs/app.log
```


#### Очистить КЕШ
#### С консоли сервера 
```bash
# Через консоль сервера 
rm -rf ./api/runtime/cache/
# Через docker
docker exec -it reg-ru-php rm -rf ./api/runtime/cache/*
```


#### Очистить картинки
#### С консоли сервера 
```bash
tail -f ./api/runtime/logs/app.log
```

#### Через docker
```bash
docker exec -it reg-ru-php rm -rf ./api/web/uploads/images/*
```