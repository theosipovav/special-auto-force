<?php

namespace app\models\dtos\request;

use Yii;
use yii\base\Model;
use app\models\entities\Product;
use app\models\entities\ProductImage;
use app\models\entities\ProductCategory;

/**
 * DTO для создания/обновления товара.
 */
class CreateProductRequest extends Model
{
    public $title;
    public $article;
    public $shortDescription;
    public $longDescription;
    public $info;
    public $price;
    public $inStock;          // может быть true/false, 1/0
    public $ordersCount;
    public $mainImage;
    public $manufacturer;
    public $country;
    public $categoryIds = [];
    public $images = [];

    public function rules()
    {
        return [
            [['title', 'shortDescription', 'longDescription'], 'required'],
            [['title', 'article', 'manufacturer', 'country'], 'string', 'max' => 255],
            [['shortDescription', 'longDescription', 'info'], 'string'],
            [['price'], 'number', 'min' => 0],
            [['inStock'], 'boolean'],
            [['ordersCount'], 'integer'],
            [['categoryIds'], 'each', 'rule' => ['integer']],
            [['images'], 'each', 'rule' => ['safe']],
            [['mainImage'], 'safe'],
        ];
    }

    public function createProduct()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $product = new Product();
            $product->title = $this->title;
            $product->article = $this->article;
            $product->short_description = $this->shortDescription;
            $product->long_description = $this->longDescription;
            $product->info = $this->info;
            $product->price = $this->price;
            $product->in_stock = (int) $this->inStock;  // true -> 1, false -> 0
            $product->orders_count = (int) $this->ordersCount;
            $product->manufacturer = $this->manufacturer;
            $product->country = $this->country;

            // Сохраняем главное изображение
            $mainImagePath = $this->saveImageFromBase64($this->mainImage, 'main');
            if ($mainImagePath !== null) {
                $product->main_image = $mainImagePath;
            }

            if (!$product->save()) {
                throw new \Exception('Ошибка сохранения товара: ' . json_encode($product->getErrors()));
            }

            // Сохраняем дополнительные изображения
            foreach ($this->images as $imgData) {
                $imagePath = $this->saveImageFromBase64($imgData ?? '', 'gallery');
                if ($imagePath === null) {
                    continue;
                }
                $productImage = new ProductImage();
                $productImage->product_id = $product->id;
                $productImage->title = $imgData['title'] ?? '';
                $productImage->image = $imagePath;


                if (!$productImage->save()) {
                    Yii::error('Ошибка сохранения изображения галереи: ' . print_r($productImage->getErrors(), true), __METHOD__);
                    throw new \Exception('Ошибка сохранения изображения галереи: ' . json_encode($productImage->getErrors()));
                }
            }

            // Сохраняем категории
            if (!empty($this->categoryIds)) {
                foreach ($this->categoryIds as $catId) {
                    $pc = new ProductCategory();
                    $pc->product_id = $product->id;
                    $pc->category_id = (int) $catId;
                    if (!$pc->save()) {
                        throw new \Exception('Ошибка привязки категории ' . $catId);
                    }
                }
            }

            $transaction->commit();
            return $product;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return null;
        }
    }

    private function saveImageFromBase64($imgData)
    {
        // Обработка разных форматов входных данных
        if (is_array($imgData)) {
            $title = $imgData['title'] ?? '';
            $base64Data = $imgData['image'] ?? '';
        } else {
            $title = 'image';
            $base64Data = $imgData;
        }

        if (empty($base64Data)) {
            return null;
        }

        // Всегда удаляем префикс data:image/...;base64,
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $extension = $matches[1];
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        } else {
            // Если префикса нет, берём расширение из title или ставим png
            $extension = pathinfo($title, PATHINFO_EXTENSION);
            if (empty($extension)) {
                $extension = 'png';
            }
        }

        $decoded = base64_decode($base64Data);
        if ($decoded === false) {
            Yii::error('Ошибка декодирования base64', __METHOD__);
            return null;
        }

        // Генерация уникального имени (UUID)
        $uuid = str_replace('-', '', generateUUID());
        $fileName = $uuid . '.' . $extension;

        $uploadPath = Yii::getAlias('@webroot/web/uploads/products/');
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0777, true) && !is_dir($uploadPath)) {
                Yii::error('Не удалось создать папку: ' . $uploadPath, __METHOD__);
                return null;
            }
        }

        $fullPath = $uploadPath . $fileName;
        if (file_put_contents($fullPath, $decoded) === false) {
            Yii::error('Не удалось записать файл: ' . $fullPath, __METHOD__);
            return null;
        }

        // Генерация URL для доступа (с учётом baseUrl /api)
        return "/api/web/uploads/products/" . $fileName;
        // return Yii::$app->urlManager->createUrl('uploads/products/' . $fileName);
    }
}
