# AVTK - zaklad backendu pro Akelu
Tento projekt vychazi z projektu do [APV](http://akela.mendelu.cz/~lysek/tmwa/). Jako
demo jsou uvedeny jednoduche REST routy, ktere pracuji s entitou person z APV. Jako
v APV je mozne vyuzivat Latte sablony a Bootstrap pro vytvoreni GUI.

## Instalace
- Pro stazeni PHP knihoven napsat `composer install` na stroji, kde je PHP a Composer
  nainstalovano.
- Zkopirovat `.env.example` na `.env` a doplnit prihlasovaci udaje k DB.
- Po zkopirovani na akelu nastavit slozku `/cache` k zapisu pro ostatni (chmod 0777).
- Po zkopirovani na akelu nastavit slozku `/logs` k zapisu pro ostatni (chmod 0777).

## Links
- [Slim framework docs](https://www.slimframework.com/docs/)
