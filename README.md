Bacula disk usage.

Utility to manage sizes of the bacula backups and locate useless data, which waste your disk space.

Usage: php du.php JobID [path] [depth]
	JobID - ID of the completed bacula job
	path  - start point, default /
	depth - depth from the startpoint, default 3

Example:

./du.php  4161 

 41.18 K        /etc/bacula/scripts/
 98.08 K        /etc/bacula/
 98.08 K        /etc/
  2.59 M        /usr/share/webacula/
  2.59 M        /usr/share/
  2.59 M        /usr/
276.65 M        /home/www/phpbee.org/
276.65 M        /home/www/
276.65 M        /home/
279.34 M        /

410 files of 279.34 M found in / for job 4161
