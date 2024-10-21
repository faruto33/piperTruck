
# PIPER TRUCK FOOD TRUCK RESERVATOION #

Pied piper est une société avec 500 salariés.

Afin d'agrémenter leur déjeuner, elle met à disposition des emplacements pour les food trucks. Quelques règles ont été mises en place pour assurer un roulement.
Du lundi au jeudi, huit emplacements sont mis à disposition.
Sept le vendredi.Chaque foodtruck ne peut réserver qu'un emplacement par semaine.Dans le but d'intégrer ces réservations au SIRH, Pied piper a besoin d'une API avec les endpoints suivants :
– Ajout d'une réservation avec une date, un nom
– Suppression d'une réservation
– Liste des réservations par jour.Étonnamment, c'est le nom du foodtruck qui sert de clé d'unicité, malgré les réserves émises.
M. Hendricks (CTO) semble s'en contenter.--


### l'API ###

L'api est documenté et testable (swagger ici):
- http://pied-piper-env.eba-mv7kxesk.eu-west-3.elasticbeanstalk.com/api/doc

#### Point d'entrée :

```http
  http://pied-piper-env.eba-mv7kxesk.eu-west-3.elasticbeanstalk.com/
```

#### Récupérer la liste de toutes les réservations goupées par jour

```http
  GET /dateStart=$dateStart&dateEnd=$dateEnd
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `dateStart` | `string` | format YYYY-mm-dd date de départ de l'interval de recherche  |
| `dateEnd` | `string` | format YYYY-mm-dd date de fin de l'interval de recherche  |

#### Ajout d'une réservation

```http
  POST /add/{id}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `json`    | `int` | **Required**. Json* contenant un object réservation à insérer|

*json format :{
  "date": "2024-10-21",
  "foodtruck": {
    "id": "string"
  },
  "placement": {
    "id": int
  }

#### Suppression d'une réservation

```http
  DELETE /delete/{id}
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `id`    | `int` | **Required**. Identifiant de la réservation à supprimer|


### le FRONTEND ###

Un petit frontend a été réalisé pour visualiser les réservations par semaine :

- http://pied-piper-env.eba-mv7kxesk.eu-west-3.elasticbeanstalk.com/schedule


## Authors

- [@farid machrou](https://github.com/faruto33/)

