## Exploring the project
### Prerequisites
* Ubunta 18.04
* PHP 7.2 & composer
* docker & docker-compose
* git

### Running
* `git clone https://github.com/GrafGenerator/php-fuzzy-duplicates.git`
* `cd php-fuzzy-duplicates`
* `sudo docker-compose build`
* `sudo bash -c ./db-bootstrap.sh` to perform initial setup of DB (this should ran once as it drops all MySQL data files).
* `sudo bash -c ./prepare.sh` to up server and create the DB if needed and apply migration (this is done always). 
* `sudo docker-compose up`
* Now project is accessible at `localhost:81`

### Test
* Fill test data in the DB
  * Perform POST request to the API `/generateDb` with following data:
  ```js
  {
    // number of clients to generate
    "clients": 10000000,
    // number of specially prepared records that will simulate
    // almost exact duplicates of existing records
    "intendedDuplicates": 100  
  }
  ```
  It will return the list of clients, each item contains original existing client and specially generated duplicate.
  
  * Perform POST request to the API `/fuzzyDuplicates`. It will return the list of fuzzy matched clients and time spent for matching. 