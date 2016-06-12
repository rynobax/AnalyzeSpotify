###################
Spotify Playlist Analyzer
###################

Spotify Playlist Analyzer is a PHP web application that gives user's 
insight into the genre's of the songs in a playlist, and how it has
changed over time.

The website is running on Heroku `here <http://stormy-journey-96158.herokuapp.com/index.php/pages/view/>`_.

*******************
Technologies Used
*******************

This application was built using the CodeIgniter Framework.  PHP and jquery 
were used to process the data, and Twitter Bootstrap and 
Chart.js were used to display the results in a pleasing way.

*******************
API's Used
*******************

-  `Spotify API <https://developer.spotify.com/web-api/>`_
-  `Last.fm API <http://www.last.fm/api>`_

*******************
Algorithm for Determining Genres
*******************

Because there are so many subgenres, I established a list of "valid" genres.
To determine the genre of a specific artist, the following steps are taken:
-  Look up the artist's top tags on Last.fm.
-  Starting with the most popular tag, check if it is a valid genre.
-  If a valid tag is found, that is considered the artist's genre.
-  If none of the tags that are > 33% popularity are valid, look up a list of related artists from Last.fm.
-  Starting with the most similar artist, repeat the previous steps until a valid genre is found.