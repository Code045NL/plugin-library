# Code Citations

## License: unknown
https://github.com/madeshv5543/zipfile/tree/a1aa7e6cfd1fc96d1661c7f3d9bda31a636462ac/html1/wp-content/plugins/download-theme/download-theme.php

```
= new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root_path), RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $file_path = $file->getRealPath();
                $relative_path
```

