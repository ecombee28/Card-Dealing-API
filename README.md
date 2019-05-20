# Card-Dealing-API
This is a Rest API that was developed in PHP with the Laravel framework. I uploaded the zip folder to the whole project as it has to many folders to uploaded individually. I also uploaded the routes folder so that you can see the raw code for this API which can be found in the web.php file.

# How to use API
To get started with the api you will need to visit:
![](images/login.png)
You JSON response will look like this returning you unique deckId number.
![](images/id.png)
## Deal Card
Once you have your deck ID you can begin to use the API.
To deal a card you enter the url http://combeecreation.com/DeckOfCards/public/api/deck/{deck_id}/deal/{num} and specifie how many cards you want to be dealt in the {num} field.
![](images/deal.png)
Once done you will recieve a response with the card count, card, and link to card image:
![](images/dealShow.png)
## Shuffle Deck
To shuffle the deck follow this url http://combeecreation.com/DeckOfCards/public/api/deck/{deck_id}/shuffle.
![](images/shuffle.png)
JSON response

![](images/shuffleShow.png)

## Discard
To discard a card from the cards that have been dealt follow this url  
http://combeecreation.com/DeckOfCards/public/api/deck/{deck_id}/discard/{dis_card}
![](images/discard.png)
JSON response
![](images/discardShow.png)

## Cut Deck
To cut the deck follow this url http://combeecreation.com/DeckOfCards/public/api/deck/{deck_id}/cut/{num}
![](images/cut.png)
JSON response
![](images/cutShow.png)

If a number is entered in that exceeds the amount of cards that are in the deck you will get a error response
![](images/)

