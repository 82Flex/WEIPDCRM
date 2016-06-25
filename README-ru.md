# WEIPDCRM #
Darwin Cydia Repository Manager

Это панель управления репозиторием с открытм исходным кодом для Saurik's Cydia™.  
Эта программа - ремэйк "[DCRM](https://github.com/tibounise/DCRM)", и мы добавили около 95% функций в эту замечательную панель.

Russian Translator: [shlyahten](https://github.com/shlyahten/WEIPDCRM-Rus)

##Посмотреть онлайн:
1.  http://cydia.shlyahten.ru - w/ Russian
2.  ~~http://apt.82flex.com~~ (closed)
3.  http://apt.touchsprite.com
4.  http://cydia.minwenlsm.pw
5.  http://apt.sunbelife.com

##Системные требования:
**минимум:**

1. PHP Version >= 5.3 и MySQL
2. MySQL или MariaDB
3. Nginx, Apache или Lighttpd

**рекомендуется:**

1. Поддержка GD и BZ2
2. GunPG Command Line Tools

##Установка и использование:
1.  Загрузите содержимое `/main/*` в свой wwwroot (корень) затем дайте файлам разрешения на чтение и запись (CHMOD).
2.  **Nginx:** Скопируйте `/readme.files/dcrm_nginx.conf` в Nginx's директорию с настройками, затем включите в конфигурацию сайта.<br/>**Lighttpd:** Включите `/readme.files/dcrm_lighttpd.conf`.
3.  Перезагрузите сервер если это требуется.
4.  Откройте `http://{YOUR_REPO_URL}/install` для преднастройки и установки DCRM.
5.  Затем войдите `http://{YOUR_REPO_URL}/manage`.
6.  Заполните пустые поля `http://{YOUR_REPO_URL}/manage/settings.php` и `http://{YOUR_REPO_URL}/manage/release.php`
7.  Загрузите deb пакет и импортируйте его либо замените старую версию новой, теперь вы можете изменять информацию *(такую как Identifier, Author, Name, Depends, etc.)*.
8.  Свободно изменяйте пакет, нажимайте на заголовок каждого поля и он заполнится автоматически. WEIPDCRM имеет хорошую страницу описания, и вы можете добавить туда скриншотов нажав на заголовок пакета.
9.  Нажмите "*Показать пакет*" или "*Скрыть пакет*" чтобы сделать его видимым или нет.
10.  Когда всё готово, нажмите "*Пересобрать*" в правом верхнем углу.
11.  Add your repository in Cydia™.

##How To Upgrade
####1.5 Pro or latter
Just upload files you get to replace. Then visit your repository homepage. It will auto update the database and configuration.
####Earlier than 1.5 Pro
Upload and replace files without `init` directory, `config.inc.php` and `connect.php` to Site Directory.
Depending on the order execution database commands from `update.log`.

##Donations
WEIPDCRM Basic and WEIPDCRM Pro are both free software, but you can donate to support the developer.

**i_82:** http://82flex.com/about

**Hintay:**  
Paypal: [![Paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ljf120%40gmail%2ecom&item_name=Hintay&item_number=WEIPDCRM&no_note=0&currency_code=USD)  
Alipay: [<img width="90" alt="Alipay" src="https://i.alipayobjects.com/i/ecmng/png/201405/2hsDKdMEqL.png">](http://blog.kugeek.com/go/alipay.html)


##License
Copyright © 2013-2016 Zheng Wu & Hintay
    
The program is distributed under the terms of the GNU Affero General Public License.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

We also offer a commercial license and technical supports, contact hintay@me.com for more details.
