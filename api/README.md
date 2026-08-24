# 📚 REST API Documentation

REST API для управления интернет-магазином (товары, категории, пользователи, заявки).

**Base URL:** `https://your-domain.com/api`

---

## 🔐 Аутентификация

API использует **JWT Bearer Token** для авторизации.

### Получение токена

```http
POST /api/auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "password123"
}
```

**Ответ:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "tokenType": "Bearer",
  "expiresIn": 604800,
  "user": { ... }
}
```

### Использование токена

Для защищённых эндпоинтов добавьте заголовок:

```http
Authorization: Bearer YOUR_JWT_TOKEN
```

или через query-параметр:

```
?access_token=YOUR_JWT_TOKEN
```

---

## 📋 Содержание

1. [Аутентификация (Auth)](#-аутентификация-auth)
2. [Товары (Products)](#-товары-products)
3. [Категории (Categories)](#-категории-categories)
4. [Изображения товаров (Product Images)](#-изображения-товаров-product-images)
5. [Заявки клиентов (Customer Requests)](#-заявки-клиентов-customer-requests)
6. [Пользователи (Users)](#-пользователи-users)
7. [Роли (Roles)](#-роли-roles)
8. [Параметры сайта (Parameters)](#-параметры-сайта-parameters)
9. [Модели данных](#-модели-данных)
10. [Обработка ошибок](#-обработка-ошибок)

---

## 🔑 Аутентификация (Auth)

### POST `/api/auth/login`

Авторизация пользователя по логину/паролю.

**Публичный эндпоинт** 🔓

**Параметры (JSON body):**

| Поле | Тип | Обязательное | Описание |
|------|-----|--------------|----------|
| `username` | string | ✅ | Логин или email |
| `password` | string | ✅ | Пароль |

**Ответ (200 OK):**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "tokenType": "Bearer",
  "expiresIn": 604800,
  "user": { /* User model */ }
}
```

**Ошибки:**
- `400` — Ошибка валидации
- `404` — Пользователь не найден
- `403` — Неверный пароль

---

### POST `/api/auth/signup`

Регистрация нового пользователя.

**Публичный эндпоинт** 🔓

**Параметры (JSON body):**

| Поле | Тип | Обязательное | Описание |
|------|-----|--------------|----------|
| `userName` | string | ✅ | Логин (уникальный) |
| `password` | string | ✅ | Пароль (мин. 6 символов) |
| `email` | string | ✅ | Email (уникальный) |
| `phone` | string | ✅ | Телефон |
| `name` | string | ✅ | Имя |
| `surname` | string | ✅ | Фамилия |
| `patronymic` | string | ❌ | Отчество |
| `address` | string | ❌ | Адрес |
| `dateOfBirth` | string | ❌ | Дата рождения |
| `image` | string | ❌ | URL аватара |

**Ответ (200 OK):**
```json
{
  "token": "...",
  "tokenType": "Bearer",
  "expiresIn": 604800,
  "user": { /* User model */ }
}
```

**Ошибки:**
- `422` — Ошибка валидации (логин/email занят, неверный формат и т.д.)

---

### GET `/api/auth/me`

Получение информации о текущем авторизованном пользователе.

**Требуется авторизация** 🔒

**Ответ (200 OK):**
```json
{ /* User model */ }
```

---

### POST `/api/auth/refresh`

Обновление JWT токена (продление сессии).

**Требуется авторизация** 🔒

**Ответ (200 OK):**
```json
{
  "token": "новый JWT токен",
  "tokenType": "Bearer",
  "expiresIn": 604800,
  "user": { /* User model */ }
}
```

---

## 🛍️ Товары (Products)

### GET `/api/products`

Получение списка товаров с фильтрацией, пагинацией и сортировкой.

**Публичный эндпоинт** 🔓

**Query-параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| `categoryId` | int | Фильтр по категории |
| `inStock` | bool | Только в наличии (`1`/`true`/`0`/`false`) |
| `q` | string | Поиск по названию, артикулу, описанию |
| `sort` | string | Сортировка: `price_asc`, `price_desc`, `popular`, `title` (по умолчанию `id_desc`) |
| `page` | int | Номер страницы |
| `per-page` | int | Элементов на странице (по умолчанию 50) |

**Ответ (200 OK):**
```json
{
  "items": [ /* массив Product */ ],
  "_links": { /* пагинация */ },
  "_meta": {
    "totalCount": 150,
    "pageCount": 3,
    "currentPage": 1,
    "perPage": 50
  }
}
```

---

### GET `/api/products/{id}`

Получение товара по ID.

**Публичный эндпоинт** 🔓

**Ответ:** [Product Model](#product)

---

### GET `/api/products/latest`

Получение последних добавленных товаров (для карусели).

**Публичный эндпоинт** 🔓

**Query-параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| `limit` | int | Количество товаров (по умолчанию 10) |

**Ответ:**
```json
{
  "success": true,
  "count": 10,
  "items": [ /* массив Product */ ]
}
```

---

### GET `/api/products/popular`

Получение самых популярных товаров (по количеству заказов).

**Публичный эндпоинт** 🔓

**Query-параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| `limit` | int | Количество товаров (по умолчанию 10) |

**Ответ:**
```json
{
  "success": true,
  "count": 10,
  "items": [ /* массив Product */ ]
}
```

---

### GET `/api/products/search`

Быстрый поиск товаров (автодополнение).

**Публичный эндпоинт** 🔓

**Query-параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| `q` | string | Поисковый запрос |
| `limit` | int | Лимит (по умолчанию 20) |

**Ответ:**
```json
{
  "success": true,
  "query": "поисковый запрос",
  "total": 5,
  "items": [ /* массив Product */ ]
}
```

---

### POST `/api/products`

Создание нового товара.

**Требуется авторизация (Admin)** 🔒👑

**Тело запроса (JSON):**

| Поле | Тип | Обязательное | Описание |
|------|-----|--------------|----------|
| `title` | string | ✅ | Название |
| `short_description` | string | ✅ | Краткое описание |
| `long_description` | string | ✅ | Подробное описание |
| `info` | string | ❌ | Доп. информация (JSON или "Ключ: Значение") |
| `article` | string | ❌ | Артикул |
| `price` | float | ❌ | Цена |
| `in_stock` | int | ❌ | В наличии (1/0) |
| `main_image` | string | ❌ | URL главного фото |
| `manufacturer` | string | ❌ | Производитель |
| `country` | string | ❌ | Страна |

**Ответ (201 Created):** [Product Model](#product)

---

### PUT/PATCH `/api/products/{id}`

Обновление товара.

**Требуется авторизация (Admin)** 🔒👑

---

### DELETE `/api/products/{id}`

Удаление товара.

**Требуется авторизация (Admin)** 🔒👑

---

### POST `/api/products/{id}/sync-categories`

Привязка товара к категориям (перезаписывает существующие связи).

**Требуется авторизация (Admin)** 🔒👑

**Тело запроса:**
```json
{
  "categoryIds": [1, 2, 5]
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Категории товара успешно обновлены",
  "product": { /* Product */ }
}
```

---

### POST `/api/products/{id}/images`

Добавление фотографии в галерею товара.

**Требуется авторизация (Admin)** 🔒👑

**Тело запроса:**
```json
{
  "title": "Фото товара сбоку",
  "image": "https://example.com/image.jpg"
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Фотография добавлена в галерею товара",
  "data": { /* ProductImage */ }
}
```

---

## 🗂️ Категории (Categories)

### GET `/api/categories`

Список всех категорий (пагинация 50).

**Публичный эндпоинт** 🔓

**Ответ:** Массив [Category Model](#category)

---

### GET `/api/categories/{id}`

Категория по ID.

**Публичный эндпоинт** 🔓

---

### GET `/api/categories/{id}/products`

Получение товаров в категории с пагинацией.

**Публичный эндпоинт** 🔓

**Query-параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| `inStock` | bool | Только в наличии |
| `sort` | string | `id_desc`, `price_asc`, `price_desc`, `popular` |
| `page` | int | Страница |

---

### POST `/api/categories`

Создание категории.

**Требуется авторизация (Admin)** 🔒👑

**Тело запроса:**

| Поле | Тип | Обязательное | Описание |
|------|-----|--------------|----------|
| `title` | string | ✅ | Название |
| `description` | string | ❌ | Описание |
| `image` | string | ❌ | URL изображения |

---

### PUT/PATCH `/api/categories/{id}`

Обновление категории.

**Требуется авторизация (Admin)** 🔒👑

---

### DELETE `/api/categories/{id}`

Удаление категории.

**Требуется авторизация (Admin)** 🔒👑

---

## 🖼️ Изображения товаров (Product Images)

### GET `/api/product-images`

Список изображений.

**Публичный эндпоинт** 🔓

**Query-параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| `productId` | int | Фильтр по товару |

---

### GET `/api/product-images/{id}`

Изображение по ID.

---

### POST `/api/product-images`

Добавление изображения.

**Требуется авторизация (Admin)** 🔒👑

**Тело запроса:**

| Поле | Тип | Обязательное |
|------|-----|--------------|
| `product_id` | int | ✅ |
| `title` | string | ✅ |
| `image` | string | ✅ |

---

### PUT/PATCH `/api/product-images/{id}`

Обновление изображения. **Admin** 🔒👑

### DELETE `/api/product-images/{id}`

Удаление изображения. **Admin** 🔒👑

---

## 📨 Заявки клиентов (Customer Requests)

### POST `/api/requests`

Создание заявки (публично). Автоматически увеличивает счётчик заказов и отправляет email администратору.

**Публичный эндпоинт** 🔓

**Тело запроса:**

| Поле | Тип | Обязательное | Описание |
|------|-----|--------------|----------|
| `phone` | string | ✅ | Телефон |
| `email` | string | ✅ | Email |
| `product_id` | int | ❌ | ID товара |
| `wishlist` | string | ❌ | Пожелания / текст |

**Ответ (201 Created):** [CustomerRequest Model](#customerrequest)

---

### GET `/api/requests`

Список заявок.

**Требуется авторизация (Manager/Admin)** 🔒👔

**Query-параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| `status` | string | `new`, `processing`, `completed`, `cancelled` |
| `page` | int | Страница |

---

### GET `/api/requests/{id}`

Заявка по ID. **Manager/Admin** 🔒👔

### PUT/PATCH `/api/requests/{id}`

Обновление заявки. **Manager/Admin** 🔒👔

### DELETE `/api/requests/{id}`

Удаление заявки. **Manager/Admin** 🔒👔

---

## 👥 Пользователи (Users)

### GET `/api/users`

Список пользователей с поиском и фильтрацией.

**Требуется авторизация (Admin)** 🔒👑

**Query-параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| `role` | string | Фильтр по названию роли |
| `q` | string | Поиск по имени, email, телефону и т.д. |
| `page` | int | Страница |

---

### GET `/api/users/{id}`

Пользователь по ID. **Admin** 🔒👑

---

### POST `/api/users`

Создание пользователя. **Admin** 🔒👑

**Тело запроса:** Аналогично [Signup](#post-apisignup)

---

### PUT/PATCH `/api/users/{id}`

Обновление пользователя.

**Доступ:** Admin 🔒👑 или сам пользователь (только свой профиль)

---

### DELETE `/api/users/{id}`

Удаление пользователя. **Admin** 🔒👑

---

### POST `/api/users/{id}/roles`

Назначение роли пользователю. **Admin** 🔒👑

**Тело запроса:**
```json
{
  "roleId": 1
}
```
или
```json
{
  "role": "admin"
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Роль успешно назначена",
  "user": { /* User */ }
}
```

---

### DELETE `/api/users/{id}/roles/{roleId}`

Отзыв роли у пользователя. **Admin** 🔒👑

**Ответ:**
```json
{
  "success": true,
  "message": "Роль успешно отозвана",
  "user": { /* User */ }
}
```

---

## 🎭 Роли (Roles)

### GET `/api/roles`

Список всех ролей. **Публичный** 🔓

### GET `/api/roles/{id}`

Роль по ID. **Публичный** 🔓

### POST `/api/roles`

Создание роли. **Admin** 🔒👑

**Тело:**
```json
{
  "title": "manager"
}
```

### PUT/PATCH `/api/roles/{id}`

Обновление роли. **Admin** 🔒👑

### DELETE `/api/roles/{id}`

Удаление роли. **Admin** 🔒👑

---

## ⚙️ Параметры сайта (Parameters)

### GET `/api/parameters`

Список параметров. **Публичный** 🔓

---

### GET `/api/parameters/map`

Получение всех параметров в виде ассоциативного массива `[code => value]`.

**Публичный эндпоинт** 🔓

**Ответ:**
```json
{
  "success": true,
  "data": {
    "site_order_email": "admin@example.com",
    "site_phone": "+7 (999) 123-45-67",
    "company_name": "ООО Компания"
  }
}
```

---

### CRUD операции

**Требуется авторизация (Admin)** 🔒👑

- `POST /api/parameters`
- `PUT /api/parameters/{id}`
- `DELETE /api/parameters/{id}`

---

## 🗃️ Модели данных

### User

```json
{
  "id": 1,
  "userName": "admin",
  "email": "admin@example.com",
  "phone": "+79991234567",
  "address": "г. Москва, ул. Примерная, 1",
  "name": "Иван",
  "surname": "Иванов",
  "patronymic": "Иванович",
  "dateOfBirth": "1990-01-15",
  "image": "https://example.com/avatar.jpg",
  "datOfRegistration": "2024-01-15 10:30:00",
  "fullName": "Иванов Иван Иванович",
  "status": 10,
  "roles": [
    { "id": 1, "title": "admin" }
  ]
}
```

### Product

```json
{
  "id": 1,
  "title": "Название товара",
  "shortDescription": "Краткое описание",
  "longDescription": "Подробное описание",
  "info": "Мощность: 100 Вт\nВес: 2 кг",
  "parsedInfo": {
    "Мощность": "100 Вт",
    "Вес": "2 кг"
  },
  "article": "ART-001",
  "price": 1500.50,
  "inStock": true,
  "ordersCount": 42,
  "mainImage": "https://example.com/product.jpg",
  "manufacturer": "Производитель",
  "country": "Россия",
  "categories": [
    { "id": 1, "title": "Категория" }
  ],
  "images": [
    {
      "id": 1,
      "title": "Фото 1",
      "image": "https://example.com/img1.jpg"
    }
  ],
  "createdAt": "2024-01-15 10:30:00"
}
```

### Category

```json
{
  "id": 1,
  "title": "Электроника",
  "description": "Электронные устройства",
  "image": "https://example.com/cat.jpg",
  "productsCount": 42
}
```

### CustomerRequest

```json
{
  "id": 1,
  "productId": 5,
  "productTitle": "Название товара",
  "phone": "+79991234567",
  "email": "user@example.com",
  "wishlist": "Хочу синего цвета",
  "createdAt": "2024-01-15 10:30:00",
  "status": "new",
  "adminNotes": "Перезвонить после 18:00",
  "product": {
    "id": 5,
    "title": "Название",
    "article": "ART-001",
    "price": 1500.50
  }
}
```

### ProductImage

```json
{
  "id": 1,
  "productId": 5,
  "title": "Фото сбоку",
  "image": "https://example.com/img.jpg"
}
```

### Role

```json
{
  "id": 1,
  "title": "admin"
}
```

### Parameter

```json
{
  "id": 1,
  "title": "Email для заявок",
  "value": "admin@example.com",
  "code": "site_order_email",
  "group": "contacts",
  "pageId": null
}
```

---

## ⚠️ Обработка ошибок

### Формат ответа при ошибке

```json
{
  "success": false,
  "message": "Описание ошибки",
  "data": {
    "fieldName": ["Ошибка валидации поля"]
  }
}
```

### HTTP статус-коды

| Код | Описание |
|-----|----------|
| `200` | Успех |
| `201` | Ресурс создан |
| `400` | Ошибка валидации / некорректные данные |
| `401` | Не авторизован |
| `403` | Доступ запрещён (недостаточно прав) |
| `404` | Ресурс не найден |
| `422` | Ошибка валидации формы |
| `500` | Внутренняя ошибка сервера |

---

## 🧪 Примеры использования (cURL)

### Авторизация
```bash
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}'
```

### Получение списка товаров
```bash
curl https://your-domain.com/api/products?categoryId=1&inStock=1&sort=price_asc
```

### Создание заявки
```bash
curl -X POST https://your-domain.com/api/requests \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+79991234567",
    "email": "user@example.com",
    "product_id": 5,
    "wishlist": "Хочу синего цвета"
  }'
```

### Создание товара (Admin)
```bash
curl -X POST https://your-domain.com/api/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Новый товар",
    "short_description": "Краткое описание",
    "long_description": "Подробное описание",
    "price": 1500,
    "in_stock": 1,
    "article": "ART-001"
  }'
```

---

## 📝 Notes

- **Пагинация:** По умолчанию 50 элементов на страницу для товаров и категорий, 20 для заявок и пользователей.
- **CORS:** API поддерживает Cross-Origin запросы из любых доменов.
- **JWT:** Токены действуют 7 дней (604800 секунд) по умолчанию.
- **Роли:** `admin` / `Администратор` имеет полный доступ, `manager` может управлять заявками, `customer` / `Клиент` — базовый пользователь.