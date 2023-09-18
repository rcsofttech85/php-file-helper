![License](https://img.shields.io/badge/License-MIT-green.svg)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/c6450a9c0f99488e93b34911f1adfb2e)](https://app.codacy.com/gh/rcsofttech85/php-file-helper/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/c6450a9c0f99488e93b34911f1adfb2e)](https://app.codacy.com/gh/rcsofttech85/php-file-helper/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)

**A simple php file helper** ✨

==========================================

**Installation**

```
composer require rcsofttech85/file-handler

```

**search by a keyword in file**

```
$temp = new TempFileHandler();
$csv = new CsvFileHandler($temp);

$findByKeyword = $csv->searchInCsvFile("movies.csv","Twilight","Film");


```

**search by a keyword in file and return array**

```
$temp = new TempFileHandler();
$csv = new CsvFileHandler($temp);

$findByKeyword = $csv->searchInCsvFile("movies.csv","Twilight","Film",FileHandler::ARRAY_FORMAT);

// output

[
    [Film] => Twilight
    [Genre] => Romance
    [Lead Studio] => Summit
    [Audience score %] => 82
    [Profitability] => 10.18002703
    [Rotten Tomatoes %] => 49
    [Worldwide Gross] => $376.66 
    [Year] => 2008


 ];
```

**Write multiple file simultaneously:**

```
$fileHandler = new FileHandler();

$fileHandler->open('file.txt');

$fileHandler->open('php://stdout');

$fileHandler->write(data: "hello world");

$fileHandler->close();

```

**converting file to an array**

```

$temp = new TempFileHandler();
$csv = new CsvFileHandler($temp);

$findByKeyword = $csv->toArray("movies.csv");
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

**Find and replace in csv file**

```

$temp = new TempFileHandler();
$csv = new CsvFileHandler($temp);

$findByKeyword = $csv->findAndReplaceInCsv("movies.csv","Twilight","Inception");

```

**Find and replace a specific keyword in a particular column of a CSV file**

```

$temp = new TempFileHandler();
$csv = new CsvFileHandler($temp);

$findByKeyword = $csv->findAndReplaceInCsv("movies.csv","Inception","Twilight",column: "Film");

```

**converting file to a json format**

```

$temp = new TempFileHandler();
$csv = new CsvFileHandler($temp);

$findByKeyword = $csv->toJson("movies.csv");

//output
[{"Film":"Zack and Miri Make a Porno","Genre":"Romance","Lead Studio":"The Weinstein Company","Audience score %":"70","Profitability":"1.747541667","Rotten Tomatoes %":"64","Worldwide Gross":"$41.94 ","Year":"2008"},{"Film":"Youth in Revolt","Genre":"Comedy","Lead Studio":"The Weinstein Company","Audience score %":"52","Profitability":"1.09","Rotten Tomatoes %":"68","Worldwide Gross":"$19.62 ","Year":"2010"},{"Film":"Twilight","Genre":"Romance","Lead Studio":"Independent","Audience score %":"68","Profitability":"6.383363636","Rotten Tomatoes %":"26","Worldwide Gross":"$702.17 ","Year":"2011"}]

```

**Encrypt a file**

```

$secret = getenv('SECRET_KEY');

$fileEncryptor = new FileEncryptor('movie.csv', $secret);
$fileEncryptor->encryptFile();

```

**Decrypt a file**

```

$secret = getenv('SECRET_KEY');

$fileEncryptor = new FileEncryptor('movie.csv', $secret);
$fileEncryptor->decryptFile();

```

**Streaming and Saving Content from URL to File**

```

 $url = "https://gist.github.com/rcsofttech85/629b37d483c4796db7bdcb3704067631#file-gistfile1-txt";
 $stream = new Stream($url, "outputFile.html");
 $stream->startStreaming();

```

**File Compression**

```

        $testFile = 'movie.csv';
        $compressedZipFilename = 'compressed.zip';

        $this->fileHandler->compress($testFile, $compressedZipFilename);

```

**File Decompression**

```

        $compressedZipFilename = 'compressed.zip';
        $extractPath = 'extracted_contents';

        $this->fileHandler->decompress($compressedZipFilename, $extractPath);

```

**File Difference**

```
vendor/bin/file-diff oldFile newFile

```

**File Integrity check**

```
$fileHasher = new FileHashChecker();

$fileHasher->hashFile(); 

$fileHasher->verifyHash($hashListFile);

```




