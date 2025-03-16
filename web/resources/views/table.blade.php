@php
$disable_sidebar = true;
@endphp
@extends('base')
@section('head')
<style>
    .poker_table {

        /* border-width: 16px;
    border-style: solid;
    border-color: rgb(24, 19, 13) rgb(109, 84, 58) rgb(109, 84, 58) rgb(24, 19, 13); */
        height: 400px;
        background-color: #3b3b3b;
        border-radius: 50px 50px 200px 200px;
        box-shadow: inset 0 0 50px #000;
        width: 840px;
        border: 25px solid #000;
        position: absolute;
    }

    .poker_card {
        width: 50px;
        transition: ease-in-out 1s;
        position: absolute;
        display: flex;
        gap: 6px;
    }

    .poker_card img {
        padding: 2px;
        background: white;
        border-radius: 4px;
    }

    .bx {
        width: 100px;
        height: 100px;
        border: 5px solid white;
        position: absolute;
    }

    .poker_card.slot {
        bottom: var(--out-deck-bottom);
        top: var(--out-deck-top);
        left: var(--out-deck-left);
        right: var(--out-deck-right);
        transform: rotate(var(--out-deck-rotate));
    }

    .poker_card.slot-1 {
        bottom: -16px;
        left: calc(50% - 10px);
        transform: rotate(0) translate(-50%, 0);
    }

    .poker_card.slot-2 {
        bottom: 60px;
        right: 35px;
        transform: rotate(0) translate(-50%, 0);
    }

    .poker_card.slot-3 {
        bottom: 60px;
        left: 40px;
    }

    .poker_card.slot-5 {
        bottom: 166px;
        left: -2px;
    }

    .poker_card.slot-4 {
        bottom: 166px;
        right: -15px;
        transform: rotate(0) translate(-50%, 0);
    }

    .poker_card.slot-6 {
        bottom: 280px;
        left: -2px;
    }

    .poker_card.slot-7 {
        bottom: 280px;
        right: -15px;
        transform: rotate(0) translate(-50%, 0);
    }

    .poker_card.slot-2.desk,
    .poker_card.slot-4.desk,
    .poker_card.slot-7.desk {
        bottom: 75%;
        right: calc(50% - 22px);
    }

    .poker_card.slot-1.desk,
    .poker_card.slot-3.desk,
    .poker_card.slot-5.desk,
    .poker_card.slot-6.desk,
    .poker_card.slot-p1.desk,
    .poker_card.slot-p2.desk,
    .poker_card.slot-p3.desk,
    .poker_card.slot-p4.desk,
    .poker_card.slot-p5.desk {
        left: calc(50% - 28px);
        transform: translate(-50%, 0);
        bottom: 75%;
    }

    .poker_card.slot .left-card,
    .poker_card.slot .right-card {
        transition: ease .5s;
        transform: translateX(0);
        background-color: white;
    }

    .poker_card.slot:not(.desk-open) .left-card {
        transform: translateX(calc(50% + 3px));
    }

    .poker_card.slot:not(.desk-open) .right-card {
        transform: translateX(calc(-50% - 3px));
    }

    .table-positions {
        display: grid;
        grid-template-columns: 1fr 300px 1fr;
        grid-template-rows: repeat(3, 1fr);
        grid-column-gap: 0px;
        grid-row-gap: 0px;
    }

    .player {
        display: flex;
        position: relative;
        transform: rotateX(90deg);
        transition: ease 1s;
    }

    .player[data-dealer="true"] .second-card::after {
        content: "D";
        position: absolute;
        left: calc(100% - 10px);
        background: #000000;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 900;
        font-size: .8rem;
        top: 0;
        transform: translateY(-50%);
    }

    .player.active {
        transform: rotateX(0deg)
    }

    .player-1 {
        grid-area: 3 / 2 / 4 / 3;
        justify-content: center;
        top: 75px;
    }

    .player-5 {
        grid-area: 2 / 1 / 3 / 2;
        right: 75px;
    }

    .player-4 {
        grid-area: 2 / 3 / 3 / 4;
        justify-content: end;
        left: 75px;
    }

    .player-3 {
        grid-area: 3 / 1 / 4 / 2;
        right: 30px;
    }

    .player-2 {
        grid-area: 3 / 3 / 4 / 4;
        justify-content: end;
        left: 30px;
    }

    .player-6 {
        grid-area: 1 / 1 / 2 / 2;
        right: 75px;
    }

    .player-7 {
        grid-area: 1 / 3 / 2 / 4;
        justify-content: end;
        left: 75px;
    }

    .slot[data-folded="true"] {
        filter: grayscale(1);
    }

    .poker_card.slot-p1:not(.desk) {
        left: calc(50% - 50px * 2.5 - 6px * 2);
        transform: translateX(calc(-50% - 3px));
        bottom: 50%;
    }

    .poker_card.slot-p2:not(.desk) {
        left: calc(50% - 50px * 1.5 - 6px * 1);
        transform: translateX(calc(-50% - 3px));
        bottom: 50%;
    }

    .poker_card.slot-p3:not(.desk) {
        left: calc(50% - 50px * 0.5);
        transform: translateX(calc(-50% - 3px));
        bottom: 50%;
    }

    .poker_card.slot-p4:not(.desk) {
        left: calc(50% + 50px * 0.5 + 6px * 1);
        transform: translateX(calc(-50% - 3px));
        bottom: 50%;
    }

    .poker_card.slot-p5:not(.desk) {
        left: calc(50% + 50px * 1.5 + 6px * 2);
        transform: translateX(calc(-50% - 3px));
        bottom: 50%;
    }

    .player img {
        transition: ease 1s;
    }

    .player[data-folded="true"] img {
        filter: grayscale(1);
    }

    .player .second-card::before {
        transition: cubic-bezier(0.25, 0.79, 0.25, 1) 1s;
        content: "FOLD";
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        background-color: white;
        height: 20px;
        top: 0;
        display: flex;
        align-items: center;
        padding: 0 2rem;
        border-radius: .5rem .5rem 0 0;
        border: 1px solid var(--bs-card-border-color);
        border-bottom: transparent;
        font-weight: 600;
        font-size: .85rem;
        color: #727272;
        z-index: -1;
    }

    .player[data-folded="true"] .second-card::before {
        top: -20px;
    }
</style>
@endsection
@section('content')
<div id="tab_loading">
    <div class="container justify-content-center d-flex flex-column align-items-center" style="padding: 250px 0;">
        <span class="spinner-border text-danger mb-4" role="status">
            <span class="visually-hidden">Loading...</span>
        </span>
        <h3>Cargando Mesa</h3>
        <p class="text-muted">Conectando al servidor.</p>
    </div>
</div>
<div class="container text-center mt-5" id="tab_table" style="display: none;">
    <h3 id="table_name">Nombre de la Mesa</h3>
    <p class="text-white badge badge-dark mb-5" id="table_description">Esperando Jugadores</p>
    <div class="d-flex justify-content-center h-400px mb-5">
        <div class="poker_table">
            <div class="position-absolute top-50 start-50 translate-middle" style="font-size: 2rem; font-family: cursive; color: #515151;">Sonkoh's Casino</div>
            <div class="position-absolute w-100 h-100 table-positions" style="z-index: 10;">
                @for($i=1; $i<=7; $i++)
                    <div class="player player-{{$i}}">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle card p-1 position-relative" style="left: .5rem; z-index:10">
                            <img src="" alt="" class="rounded-circle" style="width: 50px;">
                        </div>
                        <div class="card p-1 px-7 position-relative min-w-150px second-card" style="right: .75rem;">
                            <small class="m-0 text-gray-700 username">User</small>
                            <h3 class="m-0 fs-6">$<span class="balance">0</span></h3>
                        </div>
                    </div>
            </div>
            @endfor
        </div>
        <div class="position-absolute w-100 h-100">
            @for($i=1; $i<=7; $i++)
                <div class="poker_card slot slot-{{$i}} desk">
                <img src="/img/card.png" class="back left-card w-100">
                <img src="/img/card.png" class="back right-card w-100">
        </div>
        @endfor
        @for($i=1; $i<=5; $i++)
            <div class="poker_card slot slot-p{{6 - $i}} desk">
            <img src="/img/card.png" class="back left-card w-100">
    </div>
    @endfor
</div>
</div>
</div>
<div style="display: none;" id="buttons">
    <div class="d-flex mt-10">
        <div class="card p-2 m-auto rounded-pill">
            <div class="d-flex gap-2 rounded-pill bg-light">
                <button class="btn btn-light btn-sm rounded-pill fw-bold text-gray-600" id="bet_button">BET</button>
                <button class="btn btn-light btn-sm rounded-pill fw-bold text-gray-600" id="check_button">CHECK</button>
                <button class="btn btn-light btn-sm rounded-pill fw-bold text-gray-600" id="fold_button">FOLD</button>
                <button class="btn btn-light btn-sm rounded-pill fw-bold text-gray-600" id="raise_button">RAISE</button>
            </div>
        </div>
    </div>
</div>
<div style="display: none;" id="finish_game">
    <div class="d-flex mt-10">
        <div class="card p-2 m-auto rounded-pill">
            <div class="d-flex gap-2 rounded-pill bg-light">
                <button class="btn btn-light btn-sm rounded-pill fw-bold text-gray-600" id="show_cards">SHOW CARDS</button>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
@section('scripts')
<script>
    var budgetSlider = document.querySelector("#kt_modal_create_campaign_budget_slider");
    var budgetValue = document.querySelector("#kt_modal_create_campaign_budget_label");

    noUiSlider.create(budgetSlider, {
        start: [5],
        connect: true,
        range: {
            "min": 1,
            "max": 500
        }
    });

    budgetSlider.noUiSlider.on("update", function(values, handle) {
        budgetValue.innerHTML = Math.round(values[handle]);
        if (handle) {
            budgetValue.innerHTML = Math.round(values[handle]);
        }
    });
</script>
<script>
    class ReconnectingWebSocket {
        constructor(url, maxRetries = 100, retryDelay = 1000) {
            this.url = url;
            this.maxRetries = maxRetries;
            this.retryDelay = retryDelay;
            this.retries = 0;
            this.connect();
        }

        connect() {
            console.log('Conectando a WebSocket...');
            this.socket = new WebSocket(this.url);

            this.socket.addEventListener('open', () => {
                $("#lost_connection_alert").fadeOut(500);
                this.retries = 0;
                $.ajax({
                    type: "GET",
                    url: "/api/get_access_token",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: (response) => {
                        if (response.success) {
                            this.socket.send(JSON.stringify({
                                request: "auth.login",
                                data: response.response
                            }));
                        }
                    }
                });
            });

            this.socket.addEventListener('message', (event) => {
                console.log('Mensaje recibido:', JSON.parse(event.data));
                let response = JSON.parse(event.data);
                switch (response.request) {
                    case "auth.login":
                        if (response.success) {
                            this.socket.send(JSON.stringify({
                                request: "poker.join_table",
                                data: {
                                    table: "{{$table}}"
                                }
                            }));
                        } else {
                            window.location.href = "/";
                        }
                        break;
                    case "notification":
                        Swal.fire({
                            text: response.response,
                            icon: response.success ? "success" : "error"
                        });
                        break;
                    case "poker.join_table":
                        if (!response.success)
                            return window.location.href = "/";
                        $("#table_name").html(response.response.name);
                        $("#table_description").html(response.response.description);

                        // response.response.members.forEach(member => {
                        //     $(`.player-${member.position}`).addClass('active');
                        //     $(`.player-${member.position} img`).attr("src", member.user.avatar);
                        //     $(`.player-${member.position} .balance`).html(member.user.balance);
                        //     $(`.player-${member.position} .username`).html(member.user.username);
                        //     $(`.player-${member.position}`).attr("data-folded", response.response.playing && member.folded);
                        //     $(`.slot-${member.position}`).attr("data-folded", response.response.playing && member.folded);
                        //     if (member.me) {
                        //         $("#balance").html(`$${member.user.balance}`);
                        //     }
                        // })
                        $("#tab_loading").fadeOut(500);
                        setTimeout(() => {
                            $("#tab_table").fadeIn(500);
                            if (response.response.playing)
                                response.response.members.forEach((member) => {
                                    $(`.slot-${member.position}`).removeClass("desk");
                                    setTimeout(() => {
                                        $(`.slot-${member.position}`).addClass("desk-open");
                                    }, 1000);
                                });
                        }, 500);
                        break;
                    case "poker.table_status":
                        $("#table_name").html(response.response.name);
                        $("#table_description").html(response.response.description);

                        $('.player').each((player) => {
                            $(player).removeClass('active');
                        });

                        response.response.members.forEach(member => {
                            $(`.player-${member.position}`).attr("data-dealer", member.dealer);
                            $(`.player-${member.position}`).addClass('active');
                            $(`.player-${member.position} img`).attr("src", member.user.avatar);
                            $(`.player-${member.position} .balance`).html(member.user.balance);
                            $(`.player-${member.position} .username`).html(`[${member.position}] ${member.user.username}`);
                            $(`.player-${member.position}`).attr("data-folded", response.response.playing && member.folded);
                            $(`.slot-${member.position}`).attr("data-folded", response.response.playing && member.folded);

                            if (member.me) {
                                $("#balance").html(`$${member.user.balance}`);
                                if (response.response.game && response.response.game.turn == member.position) {
                                    let gamePrice = response.response.game.currentBet - member.bet;
                                    $("#buttons").fadeIn(500);
                                    $("#bet_button").html(`BET [$${gamePrice}]`);
                                    $("#bet_button").prop("disabled", member.balance < gamePrice);
                                    $("#bet_button").css("display", gamePrice > 0 ? "block" : "none");
                                    $("#check_button").css("display", gamePrice == 0 ? "block" : "none");
                                } else {
                                    $("#buttons").fadeOut(500);
                                }
                            }
                        });

                        (response.response.game ? response.response.game.communityCards : []).forEach((card, index) => {
                            if ($(`.poker_card.slot-p${index+1}`).hasClass("desk")) {
                                $(`.poker_card.slot-p${index+1}`).removeClass("desk");
                                $(`.poker_card.slot-p${index+1}`).css("left", "calc(50% - 50px * 2.5 - 6px * 2)");
                                new Promise((resolve) => {
                                    setTimeout(() => {
                                        $(`.poker_card.slot-p${index+1}`).css("left", "");
                                        setTimeout(() => {
                                            $(`.poker_card.slot-p${index+1} img`).css("transform", "rotateY(89deg)");
                                            setTimeout(() => {
                                                $(`.poker_card.slot-p${index+1} img`).attr("src", `/img/cards/${card.value}${card.suit}.svg`)
                                                    .on('load', function() {
                                                        $(this).css("transform", "");
                                                    });
                                            }, 500);
                                        }, 1000);
                                    }, 1000);
                                });
                            }
                        });

                        new Promise((resolve) => {
                            setTimeout(() => {
                                response.response.members.forEach(member => {
                                    if (member.hand && $(`.slot-${member.position} .left-card`).attr("src") != `/img/cards/${member.hand[0].value}${member.hand[0].suit}.svg`) {
                                        $(`.slot-${member.position} .left-card`).css("transform", "rotateY(89deg)");
                                        $(`.slot-${member.position} .right-card`).css("transform", "rotateY(89deg)");
                                        setTimeout(() => {
                                            $(`.slot-${member.position} .left-card`).attr("src", `/img/cards/${member.hand[0].value}${member.hand[0].suit}.svg`)
                                                .on('load', function() {
                                                    $(this).css("transform", "");
                                                });
                                            $(`.slot-${member.position} .right-card`).attr("src", `/img/cards/${member.hand[1].value}${member.hand[1].suit}.svg`)
                                                .on('load', function() {
                                                    $(this).css("transform", "");
                                                });
                                        }, 500);
                                    }
                                })
                                resolve();
                            }, 2000);
                        });
                        break;
                    case "poker.deal_cards":
                        response.response.forEach(slot => {
                            $(`.slot-${slot}`).removeClass("desk");
                            setTimeout(() => {
                                $(`.slot-${slot}`).addClass("desk-open");
                            }, 1000);
                        });
                        break;
                    case "poker.finish_game":
                        $("#buttons").fadeOut(500);
                        $("#finish_game").fadeIn(500);
                        break;
                }
            });

            this.socket.addEventListener('close', () => {
                window.location.href = "/";
            });

            this.socket.addEventListener('error', (event) => {
                $("#lost_connection_alert_title").html("Ha ocurrido un error");
                $("#lost_connection_alert_description").html("Error en WebSocket, reintentando...");
                $("#lost_connection_alert").fadeIn(500);
                setTimeout(() => {
                    $("#lost_connection_alert").fadeOut(500);
                }, 10000);
                this.socket.close();
            });
        }

        reconnect() {
            if (this.retries < this.maxRetries) {
                const delay = this.retryDelay * Math.pow(2, this.retries); // Retraso exponencial
                console.log(`Intentando reconectar en ${delay / 1000} segundos...`);
                setTimeout(() => {
                    this.retries++;
                    this.connect();
                }, delay);
            } else {
                console.error('Se alcanzó el máximo de intentos de reconexión');
                $("#lost_connection_alert_title").html("No se pudo reconectar");
                $("#lost_connection_alert_description").html("Verifica tu conexión e intenta recargar la página.");
            }
        }

        send(message) {
            if (this.socket.readyState === WebSocket.OPEN) {
                this.socket.send(message);
            } else {
                console.warn('No se pudo enviar, el WebSocket no está conectado');
            }
        }
    }

    const ws = new ReconnectingWebSocket('ws://127.0.0.1:8080');

    $("#bet_button").click(function(e) {
        e.preventDefault();
        ws.socket.send(JSON.stringify({
            request: "poker.bet",
            data: {
                table: "{{$table}}"
            }
        }));
    });

    $("#check_button").click(function(e) {
        e.preventDefault();
        $("#buttons").fadeOut(500);
        ws.socket.send(JSON.stringify({
            request: "poker.check",
            data: {
                table: "{{$table}}"
            }
        }));
    });

    $("#show_cards").click(function(e) {
        e.preventDefault();
        $("#finish_game").fadeOut(500);
        ws.socket.send(JSON.stringify({
            request: "poker.show_cards",
            data: {
                table: "{{$table}}"
            }
        }));
    });

    $("#fold_button").click(function(e) {
        e.preventDefault();
        $("#buttons").fadeOut(500);
        ws.socket.send(JSON.stringify({
            request: "poker.fold",
            data: {
                table: "{{$table}}"
            }
        }));
    });
</script>
@endsection