<?php

use yii\db\Migration;

/**
 * Class m260828_110529_fill_table_page
 * Заполнение таблиц начальными данными (роли, пользователь, страницы, параметры).
 */
class m260828_110529_fill_table_page extends Migration
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

        $this->insert('{{%page}}', [
            'id' => 1,
            'title' => 'Главная',
            'url' => '/',
            'description' => 'Главная страница',
            'dateCreate' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем страницу с id=1
        $this->delete('{{%page}}', ['id' => 1]);

        echo "m260828_110529_fill_table_page reverted.\n";
        return true;
    }
}