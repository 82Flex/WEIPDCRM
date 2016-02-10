# WEIPDCRM #
Darwin Cydia Repository Manager

This is an open source Repository Manager for Saurik's Cydia™ Clients.  
This program is re-designed from tibounise's "[DCRM](https://github.com/tibounise/DCRM)", and we add almost 95% functions for a wonderful repository.

##Preview:
1.  ~~http://apt.82flex.com~~ (closed)
2.  http://apt.touchsprite.com
3.  http://cydia.minwenlsm.pw
4.  http://apt.sunbelife.com

##Requirements:
**Minimum:**

1. PHP Version >= 5.3
2. MySQL or MariaDB
3. Nginx, Apache or Lighttpd

**Recommended:**

1. GD and BZ2 supports
2. GunPG Command Line Tools

##How To Install And Use:
1.  Upload `/main/*` to your wwwroot then give them read & write privileges.
2.  **Nginx:** Move `/readme.files/dcrm_nginx.conf` to Nginx's config directory, then include it in your website's config.<br/>**Lighttpd:** Include `/readme.files/dcrm_lighttpd.conf`.
3.  Restart your web server if you need.
4.  Open `http://{YOUR_REPO_URL}/install` to pre-config and install DCRM.
5.  Then login at `http://{YOUR_REPO_URL}/manage`.
6.  Fill blanks in `http://{YOUR_REPO_URL}/manage/settings.php` and `http://{YOUR_REPO_URL}/manage/release.php`
7.  Upload a package then import it or replace an older version, then you can edit its information *(such as Identifier, Author, Name, Depends, etc.)*.
8.  Edit the information of packages freely, click the title of each column and it will be autofilled. WEIPDCRM has a well organized depiction page, and you can add screenshots by clicking the title of a package.
9.  Click "*Show this package*" or "*Hide this package*" to make a package visible or not.
10.  When all is prepared, click the "*Build*" button at the right-top.
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
