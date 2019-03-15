#### 设置虚拟域名  news.com

    nginx

        server {
              listen 80;
              server_name news.com;
              # 配置为项目路径public目录
              index index.php index.html;
              root /Users/apple/projects/studynote/news;
              # 路由美化
              try_files $uri @rewrite;
              location @rewrite {
                   rewrite ^/(.*)$ /index.php?_r=$1;
              }
              access_log /Users/apple/log/news_access.log main;
              include enable_php.conf;
        }

    hosts

        127.0.0.1	news.com

#### 执行doc/init.sql文件初始化项目数据库


#### 抓取导入数据

    php doc/import.php