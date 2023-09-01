![License](https://img.shields.io/badge/License-MIT-green.svg)





**A simple php file helper** ✨



==========================================


**search by a keyword in file**

```
$fileHandler = new FileHandler();

$fileHandler->open(filename: 'movie.csv',mode:'r')->searchInCsvFile(keyword: 'Twilight',column:'Film');

```

**search by a keyword in file and return array**

```
$fileHandler = new FileHandler();

$fileHandler->open(filename: 'movie.csv',mode:'r')->searchInCsvFile(keyword: 'Zack and Miri Make a Porno',column:'Film', format: FileHandler::ARRAY_FORMAT);

// output

[
            'Film' => 'Zack and Miri Make a Porno',
            'Genre' => 'Romance',
            'Lead Studio' => 'The Weinstein Company',
            'Audience score %' => '70',
            'Profitability' => '1.747541667',
            'Rotten Tomatoes %' => '64',
            'Worldwide Gross' => '$41.94 ',
            'Year' => '2008'

        ];
```

**Write multiple file simultaneously:**

```
$fileHandler = new FileHandler();

$fileHandler->open('file.txt');

$fileHandler->open('php://stdout');

fileHandler->write(data: "hello world");

$fileHandler->close();

```

**converting file to an array**

```
$fileHandler = new FileHandler();

$data = $fileHandler->open(filename: 'movie.csv',mode:'r')->toArray();

// output
$data[0] = [
            'Film' => 'Zack and Miri Make a Porno',
            'Genre' => 'Romance',
            'Lead Studio' => 'The Weinstein Company',
            'Audience score %' => '70',
            'Profitability' => '1.747541667',
            'Rotten Tomatoes %' => '64',
            'Worldwide Gross' => '$41.94 ',
            'Year' => '2008'

        ];

```
