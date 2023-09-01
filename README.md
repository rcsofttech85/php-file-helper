![License](https://img.shields.io/badge/License-MIT-green.svg)





**A simple php file helper** ✨



==========================================


**search by a keyword in file**




```
$fileHandler = new FileHandler();

$fileHandler->open(filename: 'movie.csv',mode:'r')->searchInCsvFile(keyword: 'Twilight');

```

**Write multiple file simultaneously:**

```
$fileHandler = new FileHandler();

$fileHandler->open('file.txt');

$fileHandler->open('php://stdout');

fileHandler->write(data: "hello world");

$fileHandler->close();

```
