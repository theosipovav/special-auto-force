<?php

use yii\db\Migration;

/**
 * Class m260828_110530_fill_table_parameter
 * Заполнение таблиц начальными данными (роли, пользователь, страницы, параметры).
 */
class m260828_110530_fill_table_parameter extends Migration
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

        // Параметры (parameter)
        // 1. Общие параметры секции "Наше преимущества"
        $this->insert('{{%parameter}}', [
            'title' => 'Заголовок секции "Наше преимущества"',
            'value' => 'Преимущества заказа в СПЕЦАВТОСИЛА',
            'code' => 'our_benefit_title',
            'group' => 'our_benefit',
            'pageId' => 1,
        ]);
        $this->insert('{{%parameter}}', [
            'title' => 'Подзаголовок секции "Наше преимущества"',
            'value' => 'Специализированный технический центр комплексного снабжения нефтебаз, автоколонн и владельцев топливозаправщиков.',
            'code' => 'our_benefit_sub_title',
            'group' => 'our_benefit',
            'pageId' => 1,
        ]);

        // 2. Раздел "Выгода"
        $this->insert('{{%parameter}}', [
            'title' => 'Раздел Выгода - заголовок',
            'value' => 'Выгоды для покупателя',
            'code' => 'our_benefit_profit_title',
            'group' => 'our_benefit',
            'pageId' => 1,
        ]);
        $this->insert('{{%parameter}}', [
            'title' => 'Раздел Выгода - описание',
            'value' => 'Выгоды для покупателя',
            'code' => 'our_benefit_profit_description',
            'group' => 'our_benefit',
            'pageId' => 1,
        ]);
        $benefits = [
            ['CircleDollarSign', 'Прямые дилерские цены', 'Работаем напрямую с заводами-изготовителями (Промприбор, Ливгидромаш, Elaflex, Civacon, OPW) без торговых наценок посредников.'],
            ['Layers', 'Складской запас 5 000+ позиций', 'Постоянное наличие ходовых насосов СВН-80, СЦЛ-20/24, счетчиков ППО-40, донных клапанов и рукавов на складах в Москве и регионах.'],
            ['Award', '100% заводское качество и ГОСТ', 'Каждое изделие проходит входной контроль ОТК, сопровождается оригинальным паспортом, сертификатом ТР ТС 012 и гарантией до 24 мес.'],
            ['Truck', 'Экспресс-доставка за 1–3 дня', 'Ежедневные отгрузки транспортными компаниями (Деловые Линии, ПЭК, СДЭК) по всем регионам РФ, Казахстану и Беларуси.'],
            ['FileCheck2', 'Первичная поверка ЦСМ', 'Все расходомеры и узлы учета поставляются с действующим клеймом государственного поверителя и записью в ФГИС «АРШИН».'],
            ['Headphones', 'Персональный инженер-куратор', 'Профессиональный подбор аналогов снятых с производства импортных узлов под конкретную модель автоцистерны и тип топлива.'],
        ];
        $rows = [];
        foreach ($benefits as $index => $benefit) {
            $pos = $index + 1;
            $rows[] = ["Раздел Выгода - иконка позиция {$pos}", $benefit[0], "our_benefit_profit_{$pos}_icon", 'our_benefit', 1];
            $rows[] = ["Раздел Выгода - заголовок позиция {$pos}", $benefit[1], "our_benefit_profit_{$pos}_title", 'our_benefit', 1];
            $rows[] = ["Раздел Выгода - описание позиция {$pos}", $benefit[2], "our_benefit_profit_{$pos}_description", 'our_benefit', 1];
        }
        $this->batchInsert('{{%parameter}}', ['title', 'value', 'code', 'group', 'pageId'], $rows);

        // 3. Раздел "Услуги"
        $this->insert('{{%parameter}}', [
            'title' => 'Раздел Услуги - заголовок',
            'value' => 'Перечень предоставляемых услуг',
            'code' => 'our_benefit_services_title',
            'group' => 'our_benefit',
            'pageId' => 1,
        ]);
        $this->insert('{{%parameter}}', [
            'title' => 'Раздел Услуги - описание',
            'value' => 'Полный спектр инженерного сервиса, дооборудования и метрологии',
            'code' => 'our_benefit_services_description',
            'group' => 'our_benefit',
            'pageId' => 1,
        ]);
        $services = [
            ['Gauge', 'Лаборатория', 'Поверка и калибровка счетчиков', 'Официальная поверка и юстировка расходомеров ППО-25, ППО-40, ППВ-100, Liquid Controls с выдачей свидетельства государственного образца.'],
            ['Wrench', 'Сервис', 'Монтаж и пусконаладка насосов', 'Установка, центровка и запуск насосных агрегатов СВН-80, СЦЛ-20/24, шиберных насосов Corken/Blackmer на шасси КАМАЗ, МАЗ, УРАЛ.'],
            ['Boxes', 'Производство', 'Сборка рукавов с БРС Камлок под заказ', 'Опрессовка напорно-всасывающих рукавов МБС и Elafuel быстроразъемными соединениями Camlock и сухими разъемами Drylock любой длины.'],
            ['ShieldCheck', 'Безопасность', 'Дооборудование цистерн по ДОПОГ / ADR', 'Комплектация бензовозов системами предотвращения перелива, донными клапанами с аварийным пневмоуправлением, искрогасителями и заземлением.'],
            ['Cpu', 'Автоматизация', 'Установка контроллеров выдачи топлива', 'Интеграция электронных счетных блоков КУП, пультов дистанционного управления и систем выдачи топлива по чип-картам водителей.'],
            ['Award', 'Гарантия', 'Ремонт и поставка ремкомплектов', 'Капитальный ремонт узлов, замена торцовых уплотнений, рабочих колес, шестерен счетчиков и поставка оригинальных ЗИП со склада.'],
        ];
        $serviceRows = [];
        foreach ($services as $index => $service) {
            $pos = $index + 1;
            $serviceRows[] = ["Услуги - иконка позиция {$pos}", $service[0], "our_benefit_services_{$pos}_icon", 'our_benefit', 1];
            $serviceRows[] = ["Услуги - ярлык позиция {$pos}", $service[1], "our_benefit_services_{$pos}_badge", 'our_benefit', 1];
            $serviceRows[] = ["Услуги - заголовок позиция {$pos}", $service[2], "our_benefit_services_{$pos}_title", 'our_benefit', 1];
            $serviceRows[] = ["Услуги - описание позиция {$pos}", $service[3], "our_benefit_services_{$pos}_description", 'our_benefit', 1];
        }
        $this->batchInsert('{{%parameter}}', ['title', 'value', 'code', 'group', 'pageId'], $serviceRows);

        // 4 Раздел "Предложение консультации"
        $this->insert('{{%parameter}}', [
            'title' => 'Раздел Предложение консультации - заголовок',
            'value' => 'Нужна консультация инженера или нестандартная сборка?',
            'code' => 'offer_of_consultation_title',
            'group' => 'offer_of_consultation',
            'pageId' => 1,
        ]);
        $this->insert('{{%parameter}}', [
            'title' => 'Раздел Предложение консультации - описание',
            'value' => 'Отправьте заявку с описанием задачи или прикрепите спецификацию — наши специалисты подберут узлы, рассчитают производительность и предложат оптимальный вариант.',
            'code' => 'offer_of_consultation_description',
            'group' => 'offer_of_consultation',
            'pageId' => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем все параметры, привязанные к странице с id=1
        $this->delete('{{%parameter}}');
        echo "m260828_110530_fill_table_parameter reverted.\n";
        return true;
    }
}