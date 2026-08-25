<?php

use yii\db\Migration;

/**
 * Class m260820_112032_init
 */
class m260820_112032_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // 1. Таблица "role"
        $this->createTable('{{%role}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(64)->notNull()->unique(),
        ], $tableOptions);

        // 2. Таблица "image" (ImageEntity)
        $this->createTable('{{%image}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'path' => $this->string(255)->notNull(),
            'url' => $this->string(255)->notNull(),
        ], $tableOptions);

        // 3. Таблица "user"
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(64)->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'email' => $this->string(191)->notNull()->unique(),
            'phone' => $this->string(32)->notNull(),
            'address' => $this->string(255),
            'name' => $this->string(64)->notNull(),
            'surname' => $this->string(64)->notNull(),
            'patronymic' => $this->string(64),
            'date_of_birth' => $this->date(),
            'image_id' => $this->integer(),
            'date_of_registration' => $this->dateTime()->notNull(),
            'auth_key' => $this->string(32),
            'access_token' => $this->text(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
        ], $tableOptions);
        $this->addForeignKey('fk-user-image_id', '{{%user}}', 'image_id', '{{%image}}', 'id', 'SET NULL', 'CASCADE');

        // 4. Таблица "user_role"
        $this->createTable('{{%user_role}}', [
            'user_id' => $this->integer()->notNull(),
            'role_id' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk-user_role', '{{%user_role}}', ['user_id', 'role_id']);
        $this->addForeignKey('fk-user_role-user_id', '{{%user_role}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-user_role-role_id', '{{%user_role}}', 'role_id', '{{%role}}', 'id', 'CASCADE', 'CASCADE');

        // 5. Таблица "category"
        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'image_id' => $this->integer(),
        ], $tableOptions);
        $this->addForeignKey('fk-category-image_id', '{{%category}}', 'image_id', '{{%image}}', 'id', 'SET NULL', 'CASCADE');

        // 6. Таблица "product"
        $this->createTable('{{%product}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'short_description' => $this->text()->notNull(),
            'long_description' => $this->text()->notNull(),
            'info' => $this->text(),
            'article' => $this->string(64),
            'price' => $this->decimal(10, 2),
            'in_stock' => $this->boolean()->notNull()->defaultValue(true),
            'manufacturer' => $this->string(255),
            'country' => $this->string(64),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        // 7. Таблица "product_category"
        $this->createTable('{{%product_category}}', [
            'product_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk-product_category', '{{%product_category}}', ['product_id', 'category_id']);
        $this->addForeignKey('fk-product_category-product_id', '{{%product_category}}', 'product_id', '{{%product}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-product_category-category_id', '{{%product_category}}', 'category_id', '{{%category}}', 'id', 'CASCADE', 'CASCADE');

        // 8. Таблица "product_image"
        $this->createTable('{{%product_image}}', [
            'product_id' => $this->integer()->notNull(),
            'image_id' => $this->integer()->notNull(),
            'is_main' => $this->boolean()->notNull()->defaultValue(false),
            'title' => $this->string(255)->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk-product_image', '{{%product_image}}', ['product_id', 'image_id']);
        $this->addForeignKey('fk-product_image-product_id', '{{%product_image}}', 'product_id', '{{%product}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-product_image-image_id', '{{%product_image}}', 'image_id', '{{%image}}', 'id', 'CASCADE', 'CASCADE');

        // 9. Таблица "request" (CustomerRequest)
        $this->createTable('{{%request}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer(),
            'phone' => $this->string(32)->notNull(),
            'email' => $this->string(191)->notNull(),
            'wishlist' => $this->text(),
            'created_at' => $this->dateTime()->notNull(),
            'status' => $this->string(32)->notNull()->defaultValue('new'),
            'admin_notes' => $this->text(),
        ], $tableOptions);
        $this->addForeignKey('fk-request-product_id', '{{%request}}', 'product_id', '{{%product}}', 'id', 'SET NULL', 'CASCADE');
        
        // 10. Таблица "page"
        $this->createTable('{{%page}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'url' => $this->string(255)->notNull()->unique(),
            'description' => $this->text(),
            'dateCreate' => $this->dateTime()->notNull(),
        ], $tableOptions);

        // 11. Таблица "parameter"
        $this->createTable('{{%parameter}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'value' => $this->text()->notNull(),
            'code' => $this->string(64)->unique(),
            'group' => $this->string(64),
            'pageId' => $this->integer(),
        ], $tableOptions);
        $this->addForeignKey('fk-parameter-pageId', '{{%parameter}}', 'pageId', '{{%page}}', 'id', 'SET NULL', 'CASCADE');

        // Добавление начальных данных
        $this->seedInitialData();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем в обратном порядке создания
        $this->dropForeignKey('fk-parameter-pageId', '{{%parameter}}');
        $this->dropTable('{{%parameter}}');
        
        $this->dropTable('{{%page}}');

        $this->dropForeignKey('fk-request-product_id', '{{%request}}');
        $this->dropTable('{{%request}}');

        $this->dropForeignKey('fk-product_category-product_id', '{{%product_category}}');
        $this->dropForeignKey('fk-product_category-category_id', '{{%product_category}}');
        $this->dropTable('{{%product_category}}');

        $this->dropForeignKey('fk-product_image-image_id', '{{%product_image}}');
        $this->dropForeignKey('fk-product_image-product_id', '{{%product_image}}');
        $this->dropTable('{{%product_image}}');

        $this->dropTable('{{%product}}');

        $this->dropForeignKey('fk-category-image_id', '{{%category}}');
        $this->dropTable('{{%category}}');

        $this->dropForeignKey('fk-user_role-user_id', '{{%user_role}}');
        $this->dropForeignKey('fk-user_role-role_id', '{{%user_role}}');
        $this->dropTable('{{%user_role}}');

        $this->dropForeignKey('fk-user-image_id', '{{%user}}');
        $this->dropTable('{{%user}}');

        $this->dropTable('{{%image}}');

        $this->dropTable('{{%role}}');
    }

    private function seedInitialData()
    {
        // 1. Роли
        $this->batchInsert('{{%role}}', ['title'], [
            ['admin'],
            ['manager'],
            ['customer'],
        ]);

        // 2. Пользователь-администратор
        $this->insert('{{%user}}', [
            'username' => 'admin',
            'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
            'email' => 'admin@example.com',
            'phone' => '+70000000000',
            'name' => 'Администратор',
            'surname' => 'Главный',
            'date_of_registration' => date('Y-m-d H:i:s'),
            'status' => 10, // \app\models\User::STATUS_ACTIVE
            'auth_key' => Yii::$app->security->generateRandomString(),
        ]);

        // 3. Назначение роли админу
        $adminId = $this->db->createCommand("SELECT id FROM {{%user}} WHERE username='admin'")->queryScalar();
        $adminRoleId = $this->db->createCommand("SELECT id FROM {{%role}} WHERE title='admin'")->queryScalar();
        if ($adminId && $adminRoleId) {
            $this->insert('{{%user_role}}', [
                'user_id' => $adminId,
                'role_id' => $adminRoleId,
            ]);
        }
    }
}
