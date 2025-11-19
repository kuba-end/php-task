# Installation
* use `app` script in terminal
* `./app start`
* `./app composer install`
* `./app console doctrine:schema:create`
* `./app console doctrine:schema:create --env=test`

# Testing
* `./app tests`
* `./app phpstan`

# Usage
### Endpoint : `GET http://127.0.0.1:8888/api/payroll`
#### Sorting:
You can sort by the following fields with syntax `?sort=name` or `?sort=-name`
* name
* surname
* department
* remunerationBase
* additionAmount
* bonusType
* finalRemuneration

#### Filtering:
You can filter by the following fields with syntax`?filter[name]=Vernon`
* name
* surname
* department

#### Mix it up
Or you can mix it up `?filter[name]=Vernon&filter[surname]=Lindgren&sort=-additionAmount`


# Fixtures
* `./app console app:db:populate`
* `./app console app:db:truncate`

