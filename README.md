## Exploring the project
### Prerequisites
* Ubunta 18.04
* PHP 7.2 ( with php7.2-xml and php-mysql) & composer
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
* Fill test data in the DB - perform POST request to the API `/generateDb` with following payload:
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
    Clients generated in deterministic manner, duplicates are always inserted last, and duplicate the first `intendedDuplicates` clients, so, f.e. if we have 1000 clients and 100 duplicates, duplicates will be at ids 901-1000 and duplicate ids 1-100, so 901 duplicates 1, 902 duplicates 2 and so on.
    
    **Note:** this operation clears any clients that were earlier in the DB.  

* Find duplicates using fuzzy matching in PHP - perform POST request to the API `/fetchDuplicatesPhp` with the following payload: 
    ```json
    {
        "matchThreshold": 90
    }
    ``` 
    where `matchThreshold` is a threshold for ssdeep score result, search will take records that matching with score more than threshold. Ssdeep score can be explained as percentage of similarity and can take values from 0 to 100, thus the `matchThreshold` too.
    This request will return the list of fuzzy matched clients and time spent for matching. 
    
* Also you can find duplicates using fuzzy matching on MySQL side - perform POST request to the API `/fetchDuplicatesSql`. Request and response formats are the same as above.