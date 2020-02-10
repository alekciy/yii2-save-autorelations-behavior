Yii2 Active Record Save Auto Relations Behavior
==========================================
Данное поведение расширяет [yii2-save-relations-behavior](https://packagist.org/packages/la-haute-societe/yii2-save-relations-behavior)
автоматически создавая связи на основании заданной конфигурации.

Установка
------------

Через [composer](http://getcomposer.org/download/):
```
composer require --prefer-dist alekciy/yii2-save-autorelations-behavior
```

Конфигурирование
-----------

В модель нужно лишь дописать связанную с ней другую модель. Для связи 1-n в свойство `manyRelationList`, для 1-1 в `oneRelationList`.
Например, нужно добавить в класс машины `Car` связь с водителями `Driver`:

```php
use yii\db\ActiveRecord;
use alekciy\Yii2SaveAutoRelationsTrait;
use alekciy\Yii2SaveAutoRelationsBehavior;

class Car extends ActiveRecord
{
    use Yii2SaveAutoRelationsTrait; // Необязательно, но нужно для загрузки связи через loadRelations()

    public function behaviors()
    {
        return [
            'saveRelations' => [
                'class' => Yii2SaveAutoRelationsBehavior::className(),
                'manyRelationList' => [
                    'drivers' => Driver::class,
                ],
            ],
        ];
    }
}
```
Вот и все. Не нужно создавать `getDrivers()` метод. Поведение автоматически создаст таблицу связей (имя начинается с
префикса `link__`). С остальными вариантами использования можно ознакомиться на странице
 [Yii2 Active Record Save Relations Behavior](https://github.com/la-haute-societe/yii2-save-relations-behavior#usage)
