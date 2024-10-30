=== CbrRate ===
Plugin Name: Cbr Rate
Plugin URI: http://selikoff.ru/tag/cbrrate/
Description: Виджет курса валют ЦБ РФ на текущий день.
Version: 1.1
Author: Selikoff Andrey
Author URI: http://www.selikoff.ru/
Contributors: AndreyS.
Donate link: http://www.selikoff.ru/
Tags: rate, currency, exchange, rouble, CBR, RUB, EUR, USD
Requires at least: 4.0
Tested up to: 4.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show currency exchange rate Central Bank of Russia
Виджет курса валют ЦБ РФ на текущий день.

== Description ==

* Show the exchange rate Central Bank of Russian Federation. This bank sets the exchange rate once a day on weekdays. To display dynamic exchange rate using the module MoExRate.
* Отображение курса валют ЦБ РФ на текущий день c динамикой изменения курса RUR, RUB, EUR, USD. Устанавливается банком один раз в день. Для отображения динамического биржевого курса используйте модуль MoExRate

**Supported Languages:**

* RU Russian (default)

== Screenshots ==

1. This screen shot screenshot-1.jpg of use widget CbrRate on same theme

== Installation ==

1. Unzip archive to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. To add the CbrRate widget to the sidebar go to 'Appearance->Widgets', and add the CbrRate to your blog.
4. You must run script for give currency rate info. This script contains three test url:
   /cbrtest - test reading external xml file
   /cbrup - update currency rate date from external xmlfile
   /cbrread - test reading saved currency rate date
5. wp-cron run this script hourly if your cron success configured.
   Start wp-cron if it is not running, examples:
   GET http://site.ru/wp-cron.php
   or
   wget -q -O - http://site.ru/wp-cron.php > /dev/null 2>&1
   or
   /opt/php/5.1/bin/php-cgi -f /var/www/user_id/data/www/site.ru/wp-cron.php


* Для включения виджета, после активации плагина переходим в:
* Внешний вид
* Виджеты
* Перетаскиваем виджет CbrRate на панель сайдбара
* Запускаем вручную для первоначального получения данных /cbrup
* конфигурируем крон если он не сконфигурирован


== Frequently Asked Questions ==
* Please, send your questions about this widget to my e-mail: selffmail@gmail.com
* Пожалуйста, все вопросы по работе виджета направляйте на: selffmail@gmail.com
