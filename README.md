PHPKeywordGenerator
===================

PHP keyword generator

This mini project was created to find an automated way to generate meta keywords for any given website.

The code works by receiving a website and retrieving the content using cURL. It then goes through many iterations removing miscellaneous content. The script uses two word lists to loop through and remove common and irrelevant words. Next, the script counts the amount of occurrences a particular word appears on the site and then orders them in descending order. It finally creates the meta keyword list by joining the words together in the correct format. A option that was added to the script was to be able to set a limit on the minimum occurrences a word has to appear before it can be used as a keyword which is useful if the website contains little content.

Although the script works relatively well, keyword SEO will always be best when done manually by a user as the content can be evaluated into more detail.

A live version of this script can be viewed here - http://keyword.michaelchidley.co.uk
