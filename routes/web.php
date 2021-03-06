<?php
/*
* Author: Eric Combee
* Date: 5/15/2019
*/
use Illuminate\Http\Request;
use App\MasterDeck;
use phpDocumentor\Reflection\Types\Object_;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
* This route must be called first to get your deck_id. With out a deck_id
* you are not able to use the rest of the api.
*/

Route::get('/api/newdeck', function () {

    $alph = [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',
        'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1',
        '2', '3', '4', '5', '6', '7', '8', '9'
    ];

    $deckId = '';
    // This creates a random deckId from the alph[].
    for ($i = 0; $i < 7; $i++) {
        $deckId .= $alph[rand(0, count($alph) - 1)];
    }

    DB::table('deck_users')->insert(
        ['deck_id' => $deckId]
    );

    $master_deck = DB::select('select cards from master_deck');

    for($i=0;$i<count($master_deck);$i++){
      DB::insert('insert into user_decks (deck_id, card) values (?, ?)', [$deckId, $master_deck[$i]->cards]);
    }



    $response = (Object)[
        'status' => 'OK',
        'DeckID' => $deckId
    ];

    return response()->json($response);
});

/*
* This route deals a number {num} of cards. Once the card has been dealt
* it is put in the dealt pile and erased from the user_decks pile.
*/
Route::get('/api/deck/{deck_id}/deal/{num}', function ($deck_id, $num) {

    $count = DB::table('user_decks')->select('card')->where('deck_id', '=', $deck_id)->count();

    if ($num > $count) {
        $object = (object)[
            'Error' => 'Insufficient card count. Try again',
            'Card Count' => $count,

        ];
        return response()->json($object);
    } else {

        
        // this deals the cards
        $cards = DB::select('select u.deck_id, u.card, m.card_image from master_deck m 
                          join user_decks u where u.deck_id = ? and u.card = m.cards order by u.id limit ?', [$deck_id, $num]);


        for ($i = 0; $i < $num; $i++) {
            DB::insert('insert into dealt (deck_id, card) values (?,?)', [$deck_id, $cards[$i]->card]);
            DB::delete('delete from user_decks where card = ? and deck_id = ?', [$cards[$i]->card, $deck_id]);
        }


        $count = DB::table('user_decks')->select('card')->where('deck_id', '=', $deck_id)->count();

        $response = (Object)[
            'Card_Count' => $count,
            'Cards' => $cards
        ];


        return response()->json($response);
    }
});

/*
* This route shuffles the cards that are remaining in the deck.
*
*/

Route::get('/api/deck/{deck_id}/shuffle', function ($deck_id) {
    $card_id = "";
    $id = 0;
    global $shuffled;


    $count = DB::table('user_decks')->select('card')->where('deck_id', '=', $deck_id)->count();

    $remainingCards = DB::select('select u.deck_id, u.card, m.card_image from user_decks u 
                       join master_deck m where u.deck_id = ? and u.card = m.cards', [$deck_id]);
    $remainingCards = collect($remainingCards);

    $shuffled = $remainingCards->shuffle();

    $shuffled->all();
    json_encode($shuffled);

    for ($i = 0; $i < ($count); $i++) {

        $card_id = DB::select('select cards from master_deck where cards = ?', [$shuffled[$i]->card]);
        if ($i == 0) {
            DB::delete('delete from user_decks where deck_id = ?', [$deck_id]);
        }
        DB::insert('insert into user_decks (deck_id, card) values (?,?)', [$shuffled[$i]->deck_id, $shuffled[$i]->card]);
    }
    $response = (Object)[
        'Cards_Shuffled' => 'true',

    ];
    return response()->json($response);
});

/*
* This route will cut the deck from a spot that is selected by the user
* through the url in the {cut_num} section. The deck is but into 2 section
* at the point of the {cut_num} and the top half is put at the end of the bottom half.
*/

Route::get('/api/deck/{deck_id}/cut/{cut_num}', function ($deck_id, $cut_num) {

    $count = DB::table('user_decks')->select('card')->where('deck_id', '=', $deck_id)->count();

    if ($cut_num > $count) {
        $response = (Object)[
            'Cards_Cut' => 'false. Invaild cut number.',

        ];
        return response()->json($response);
    } else {

        $deck = DB::select('select id,card from user_decks where deck_id = ? order by id', [$deck_id]);
    
        $crt =0;
        $crt2 = 0;
       
        for($i=$cut_num;$i< $count;$i++){
            $final_deck[$crt] = $deck[$i]->card;
            $crt++;
        }
        for($j=$crt;$j<$count;$j++){
            $final_deck[$j] = $deck[$crt2]->card;
            $crt2++;
        }

        DB::delete('delete from user_decks where deck_id = ?', [$deck_id]);

        for($i=0;$i<count($final_deck);$i++){
            DB::insert('insert into user_decks (deck_id, card) values (?, ?)', [$deck_id, $final_deck[$i]]);
        }
        

        $response = (Object)[
            'Cards_Cut' => 'true',

        ];
        return response()->json($response);
    }
});

/*
* This route reordders the deck into the original order according to what cards are 
* left in the deck.
*/
Route::get('/api/deck/{deck_id}/reorderdeck', function ($deck_id) {

    $curr_deck = DB::select('select card from user_decks where deck_id = ?', [$deck_id]);
    $master_deck = DB::select('select cards from master_deck');

    DB::delete('delete from user_decks where deck_id = ?', [$deck_id]);

    for ($i = 0; $i < count($master_deck); $i++) {

        for ($j = 0; $j < count($curr_deck); $j++) {
            if (strcmp($master_deck[$i]->cards . '', $curr_deck[$j]->card . '') == 0) {
                DB::insert('insert into user_decks (deck_id, card) values (?, ?)', [$deck_id, $master_deck[$i]->cards]);
            }
        }
    }



    $response = (Object)[
        'Deck_Reordered' => 'true',

    ];
    return response()->json($response);
});
/*
* This route will discard a card {dis_card} that is in the dealt pile.
*/
Route::get('/api/deck/{deck_id}/discard/{dis_card}', function ($deck_id, $dis_card) {
    $response = null;

    $count =  DB::select('select count(card) as count from dealt where deck_id = ? and card = ?', [$deck_id, $dis_card]);
   // If the dis_card is in the dealt pile
    if ($count[0]->count == 1) {
        DB::insert('insert into discard (deck_id, dis_card) values (?, ?)', [$deck_id, $dis_card]);
        DB::delete('delete from dealt where deck_id = ? and card = ?', [$deck_id, $dis_card]);

        $response = (Object)[
            'Card_Discarded' => $dis_card . ' was discarded.',
            'Status' => 'OK'

        ];
    } else {
        $response = (Object)[
            'Card_Discarded' => $dis_card . ' was not found in the dealt pile.',

        ];
    }

    return response()->json($response);
});

/*
* This route just restarts the api with a new deck and with dealt and discard is cleared of the 
* the current deck_id.
*/
Route::get('/api/deck/{deck_id}/startover', function ($deck_id) {

    DB::delete('delete from discard where deck_id = ?', [$deck_id]);
    DB::delete('delete from dealt where deck_id = ?', [$deck_id]);
    DB::delete('delete from user_decks where deck_id = ?', [$deck_id]);


    $master_deck = DB::select('select cards from master_deck');

    for($i=0;$i<count($master_deck);$i++){
      DB::insert('insert into user_decks (deck_id, card) values (?, ?)', [$deck_id, $master_deck[$i]->cards]);
    }

    $response = (Object)[
        'Deck_Reinitialized' => 'true',

    ];
    return response()->json($response);
});
