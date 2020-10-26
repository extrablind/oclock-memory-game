<template>
<div class="vueJsContainer">
    <div class="row">
        <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">

            <!-- Display this on win or lose game only -->
            <div class="winLoseContainer" v-if="status=='win' || status=='lose'">
                <h3 v-if="status=='win'">It's a win ! Congratulations !</h3>
                <h3 v-if="status=='lose'">Time is up, you lose ! Sorry {{username}} ! </h3>
                <div class="input-group" v-if="status=='win'">
                    <input type="text" v-model="username" class="form-control" placeholder="Your 3 letters name for posterity..." name="name" maxlength="3" />
                    <!-- We save score on user request, see save() in methods -->
                    <div class="input-group-append" id="button-addon4">
                        <button v-on:click="save()" v-bind:disabled="disableUsernameInput" class="btn btn-outline-secondary">Save your score</button>
                    </div>
                </div>

                <hr>
            </div>

            <!--Main area -->
            <div class="boardGame" v-bind:class="{
                'boardBlurry': status == 'win' || status == 'lose'}">
                <!-- Loop over each cards  -->
                <span v-for=" card in cardsInGame" class="playingCardContainer">
                    <!-- cheat display ;) -->
                    <!-- {{ card.key }}
                    {{ card.index }} -->
                    <!-- Vary class depending of the card status that changes during game -->
                    <span v-bind:class="{
                        'selected': card.status =='selected',
                        'won': card.status =='won',
                        'unselected': card.status =='unselected',
                        // Set to true = always visble
                        'playingCard':true ,
                        // This is a way to concatenate object property with text.
                        ['card-' + card.key]:true} " v-on:click="actionClickOnCard(card)">
                    </span>
                </span>
            </div>
        </div>
        <!-- Aside area : score, restart...etc -->
        <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
            <div class="progress">
                <div class="progress-bar" role="progressbar" v-bind:style="{ width: progress+ '%' }" v-bind:aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">{{progress}}%</div>
            </div>
            <hr>
            <button type="button" v-on:click="restart" class="btn btn-primary btn-lg btn-block">Restart</button>
            <hr>
            <h4>Score : <small>{{score}}</small></h4>
            <h4>Turn : <small>#{{turn}}</small></h4>
            <h4>Remaining Time : <small>{{timer}} sec.</small></h4>
            <h4>Pairs found :<small> {{wonPairs}} / {{NB_PAIRS}}</small></h4>

            <p v-if="status == 'pending'"></p>
            <p>{{message}}</p>

            <!-- Only display this if we've got a list of scores to display -->
            <section class="highScores" v-if="scores.length > 0">
                <hr>
                <h3>Wall of fame </h3>
                <ul>
                    <!--Loop over scores retrieved from ajax request -->
                    <li v-for="score in scores">
                        <span class="badge badge-secondary ">{{score.name}}</span> - {{score.score}} points in {{score.time}} secs
                    </li>
                </ul>
            </section>
        </div>
    </div>
</div>
</template>

<script>
//==============================================================================
// Imports are required libs for this game, install them with yarn install <theLib>
import Vue from 'vue'
// See moment.js lib to manipulate date easily : https://momentjs.com/
import moment from 'moment'
// LoDash is a toolkit lib for many purpose, https://lodash.com/ : array manipulation and more... learning to love it
import _ from 'lodash'
// Animation class : https://animate.style/ - NOT USED - interesting for future evolutions
// import animate from 'animate.css'
// Axios is a lib to make request easier : https://github.com/axios/axios#example
const axios = require('axios').default;
//==============================================================================
// App constant to define timeout gameover bell in seconds : 3 minutes (* 60 sec in a min.)
const TIME_TO_FINISH = 3 * 60;
// App constant : you win the game when 14 pairs have been successfully found
const NB_PAIRS = 15;
// Define all posible cards, key is the representation of css class available in assets/scss/memory/main.scss
const CARDS = [{
        key: 'redApple',
        name: 'Red Apple',
    },
    {
        key: 'orange',
        name: 'Orange',
    },
    {
        key: 'granada',
        name: 'Granada',
    },
    {
        key: 'apricot',
        name: 'Apricot',
    },
    {
        key: 'yellowLemon',
        name: 'Yellow Lemon',
    },
    {
        key: 'strawberry',
        name: 'Strawberry',
    },
    {
        key: 'greenApple',
        name: 'Green Apple',
    },
    {
        key: 'peach',
        name: 'Peach',
    },
    {
        key: 'grappes',
        name: 'Grappes',
    },
    {
        key: 'watermelon',
        name: 'Watermelon',
    },
    {
        key: 'plum',
        name: 'Plum',
    },
    {
        key: 'pear',
        name: 'Pear',
    },
    {
        key: 'redCherry',
        name: 'Red Cherry',
    },
    {
        key: 'raspberry',
        name: 'Raspberry',
    },
    {
        key: 'mongo',
        name: 'Mongo',
    },
    {
        key: 'yellowCherry',
        name: 'Yellow Cherry',
    },
];

//==============================================================================
// Beginning of the game definition
export default {
    // The name of the component, we have only one component for simplicity, so leaving "App" is ok.
    name: 'App',
    // We use no other vuejs component
    components: {},
    // Data : vars that will be used and edited in our app and maybe displayed in template
    data: function() {
        return {
            // Cards that will be really displayed during game : limit to 14 * 2
            cardsInGame: [],
            // Array to save flipped cards, max 2 in array
            flipped: [],
            // Count turns for the game : each 2 cards clicked is a turn
            turn: 1,
            // Score initialized at 0
            score: 0,
            // Is the game started ? : when first click on a card, this will become true
            started: false,
            // A display for timer : time spent during the game
            timer: '-',
            // 0-100 indicator for progress bar, see begin function()
            progress: 0,
            // Number of founded pair of card, to calculate the score and the end of game
            wonPairs: 0,
            // A status : playing, pending, win, lose for easy display div in template
            // TODO : Add a pause button and a pause status
            status: 'begin',
            // Access constant in template
            NB_PAIRS: NB_PAIRS,
            // High Scores list array
            scores: [],
            // User input at the end of the game has one field : username, we need to bind (link) this value
            username: '',
            // Disable username input button when score is saved : avoid double score saving
            disableUsernameInput: false,
            // A string to display messages
            message: '',
        }
    },
    // When component is created, execute this function
    created: function() {},
    // When component is fully loaded and operationnal execute this function
    mounted: function() {
        // We reset all params and distribute cards...
        this.restart();
    },
    // Methods can be called from template (top of the page) or from other methods
    methods: {
        // Restart the game, put values to default, new cards
        restart: function() {
            // Ensure to clean the game board
            this.cardsInGame = []
            // Ensure to kill the countdown
            clearInterval(this.countdown);
            // Randomize the whole series, all the cards with lodash lib
            let randomizedCards = _.shuffle(CARDS);
            // Reduce the array to NB_PAIRS card : for exemple we need 28 cards so 14 is to be defined, but we can change difficulty  by editing NB_PAIRS constant if we stay under NB_PAIRS.length
            let reduce = randomizedCards.slice(0, NB_PAIRS);
            // This is a hack for a deep copy of array : when you copy a array of several object, objects are kept by reference, so editing one object in an array will affect the other same object in the other array. Another way to do the same tricks is to convert to array json  and convert back to object, so the reference is lost => do => let clone = JSON.parse(JSON.stringify(reduce)
            let clone = reduce.map(object => Object.assign({}, object));
            // Double same array to get 28 cards : 2 identical img, using lodash lib
            let cards = _.concat(clone, reduce)
            // Add some more property to our playing cards
            cards.forEach(function(element, i) {
                element.status = 'unselected';
                // Adding a numeric index for comparaison
                element.index = i;
            });
            // Shuffle again : the order must not be predictable
            this.cardsInGame = _.shuffle(cards);
            // Reset data to default values, usefull for restarting game.
            // This will override default values
            this.started = false;
            this.score = 0;
            this.wonPairs = 0;
            this.timer = '-';
            this.turn = 1;
            this.progress = 0;
            this.flipped = [];
            this.status = 'pending';
            this.username = '';
            this.message = 'Start the game by clicking any card...';
            this.disableUsernameInput = false;
            // Retrieve last score from db with ajax request
            this.retrieveLastScores();
        },
        retrieveLastScores: function() {
            // GET request using axios
            axios.get('/game/oclock-memory/high-score')
                .then(
                    response => {
                        this.scores = response.data
                    })
                .catch(function(error) {
                    // handle error
                    console.log(error);
                })
        },
        // First click on game is considered as equivalent for starting the game : beginning countdown and progress bar
        begin: function() {
            // Init vars
            // Vue need to be available in the anonymous function of setInterval() : see closure.
            var that = this;
            // See moment.js lib object
            // We adding xx seconds to current time
            var end = moment().add(TIME_TO_FINISH, 'seconds')

            // Game status changes
            this.status = 'playing'

            // Start countdown : define a new interval
            this.countdown = setInterval(function() {
                    // Get date and time for now on each loop
                    var now = moment();
                    // Get the time difference between now and the future gameOver limit date (var end)
                    var diffInSeconds = Math.floor(end.diff(now, 'seconds', true));
                    // Update timer
                    that.timer = diffInSeconds;
                    // Scale seconds from 1 to 100 to get a valid number to be displayed in progress bar
                    that.progress = 100 - Math.floor((diffInSeconds * 100) / (TIME_TO_FINISH));

                    // The count down is finished, this is game over, sorry !
                    if (diffInSeconds <= 0) {
                        // F12 is the secret to see that...
                        console.log('Time is up ! Game over ! Kitten are angry now !');
                        that.status = 'lose'
                        // Destroy interval, it will be re-created on next game
                        clearInterval(that.countdown);
                    }
                },
                // Interval is executed every second (1000ms)
                1000);
        },
        // Method to get score based on several game vars
        reCalculateScore: function() {
            /*
                Score is a ratio between try to guess (turns) and really won pair.
                This is ponderated by the remaining progress bar (time representation)
                    if we've found a pair without too much try guess : more point
                    if we've found a pair at a lower time  (rapidity) : more point
            */
            let score = Math.floor(this.wonPairs / this.turn + (100 - this.progress));
            // Increment current score
            this.score += score
            console.log("Adding to score : " + score + '. Score is now ' + this.score)

        },
        // Main method. card in param is the current clicked card.
        actionClickOnCard: function(card) {
            // If status is the end of game. We can not play, so clicking on card will do nothing
            if (this.status == 'win' || this.status == 'lose') {
                return;
            }
            // Game not started, pre-flight check !
            // NOTE: We should probably merge this.started with this.status var...
            if (!this.started) {
                // Start chrono
                this.begin();
                // Save game state
                this.started = true;
            }
            // We  have clicked on already won card, do nothing
            if (card.status == 'won') {
                return;
            }

            // We have played one card and clicked on the same, do nothing and stop here
            if (this.flipped.length == 1 && card.index == this.flipped[0].index) {
                return
            }
            // Change status and therefore class and therefore aspect of target : set visible. In short : flip the card.
            // See css to understand fade effect based on classname
            card.status = 'selected'

            // We save the new clicked card in the dedicated array of current played card : 2 cards by turn
            this.flipped.push(card)

            // End of a turn : we have clicked twice, so selected a pair of card
            if (this.flipped.length === 2) {
                // Winning this pair : is this the same key ?
                if (this.flipped[0].key === this.flipped[1].key) {
                    console.log("Winning this pair. Kitten are making a party !")

                    // Mark cards as won !
                    this.message = "Winning pair " + card.name;
                    card.status = 'won';
                    this.flipped[0].status = 'won';

                    // Increments pair found var.
                    this.wonPairs++
                    // Calculate score every pair won
                    this.reCalculateScore()
                }
                // Losing this pair
                else {
                    console.log("Losing this pair.. Kitten are sad.")
                    var firstCard = this.flipped[0]
                    // Before disappear the card must be shown a little moment, change class after a delay
                    let timeout = setTimeout(function() {
                        card.status = 'unselected';
                        firstCard.status = 'unselected';
                    }, 700)
                }
                // Anyway, a new turn is beginning, empty played card array
                this.flipped = [];
                // Increment turn var
                this.turn++;
            }
            // For debugging purpose...
            // else {
            // console.log("Continue playing a second card...");
            // }
            // We have found all pairs ! We win the game in time ! We are the champion  my friend !
            if (this.wonPairs === NB_PAIRS) {
                console.log('You win the game !! Kitten are in a hurry to go dancing !');
                this.status = 'win'
                // Extra points for a win ! Just for fun.
                this.score += 1000;

                // Stop the countdown
                clearInterval(this.countdown);
            }
        },

        save: function() {
            // Post params needs to be encoded in form-data to work properly with PHP
            var bodyFormData = new FormData();
            // Post params here
            bodyFormData.append('username', this.username);
            bodyFormData.append('score', this.score);
            bodyFormData.append('time', TIME_TO_FINISH - this.timer);
            // Axios POST request. Same as ajax.
            axios({
                    method: 'post',
                    url: '/game/oclock-memory/save',
                    data: bodyFormData,
                    // PHP Also needs a specific content-type
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                // When promise comes back, get last score to update the list. Disable user input.
                //  In short promise are to be seen as a callback without hell indentation
                .then((response) => {
                    this.message = 'Score saved !'
                    this.retrieveLastScores();
                    this.disableUsernameInput = true
                })
                // Retrieve any error launched by any promise up
                .catch(function(error) {
                    console.log(error);
                });
        }
    },
}
</script>
