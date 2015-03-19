# DCRM #
Darwin Cydia Repository Manager<br />
This is an open source Repository Manager for Saurik's Cydia Clients.<br />

###Online View:<br />
1.  http://apt.82flex.com<br />
2.  http://apt.touchsprite.com<br />
3.  http://apt.phoneai.cn<br />
4.  http://cydia.so<br />
5.  http://cydia.minwenlsm.pw<br />

###Requirements:<br />
1. PHP Version >= 5.3, MYSQL<br />
2. GD and BZ2 supports.<br />
3. Nginx, Apache or Lighttpd<br />

###How To Install And Use:<br />
1.  Upload /main/* to your wwwroot then give them read & write privileges;<br />
2.  Nginx: Move /readme.files/dcrm_nginx.conf to Nginx's config directionary, then include it in your website's config.<br />
    Apache: Rename /readme.files/dcrm_apache.htaccess to $wwwroot/.htaccess;<br />
3.  Restart Nginx;<br />
4.  Open http://{YOUR_REPO_URL}/install to create MYSQL tables.<br />
5.  Then login at http://{YOUR_REPO_URL}/manage.<br />
6.  Fill in blanks in http://{YOUR_REPO_URL}/manage/settings.php and http://{YOUR_REPO_URL}/manage/release.php<br />
7.  Upload a package then import it or replace an old version, then you can edit its information (such as Identifier, Author, Name, Depends, etc.).<br />
8.  Edit the information of packages freely, click the title of each column and it will be autofilled. WEIPDCRM has a well organized depiction page, and you can add screenshots by clicking the title of a package.<br />
9.  Click "Show this package" or "Hide this package" to make a package visible or not.<br />
10.  When all is ready, click the "Build" button at the right-top.<br />
11.  Add your repo in Cydia.<br />

>This program is re-designed from tibounise's "DCRM", and I add almost 95% functions for a wonderful repo.<br />

    Copyright © 2013-2015 Zheng Wu & Hintay
    
    The program is distributed under the terms of the GNU General Public License (or the Lesser GPL).

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    

