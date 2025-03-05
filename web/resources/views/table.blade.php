@extends('base')
@section('head')
<style>
    .poker_table {

        /* border-width: 16px;
    border-style: solid;
    border-color: rgb(24, 19, 13) rgb(109, 84, 58) rgb(109, 84, 58) rgb(24, 19, 13); */
        height: 400px;
        background-color: #00548e;
        border-radius: 50px 50px 200px 200px;
        box-shadow: inset 0 0 50px #000;
        max-width: 840px;
        border: 25px solid rgb(0, 43, 72);
        position: absolute;
    }

    .poker_card {
        width: 50px;
        border-radius: 1px;
        transition: ease-in-out 1s;
        position: absolute;
        display: flex;
        gap: 5px;
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
        bottom: 45px;
        left: 45px;
        transform: rotate(45deg) translate(-50%, -50%);
    }

    .poker_card.slot-2 {
        bottom: 45px;
        right: 45px;
        transform: rotate(-45deg) translate(-50%, -50%);
    }

    .poker_card.slot-3 {
        top: 25px;
        left: 25px;
        transform: rotate(90deg);
    }

    .poker_card.slot-4 {
        top: 25px;
        right: 25px;
        transform: rotate(90deg);
    }

    .poker_card.slot-5 {
        bottom: 25px;
        left: 250px;
    }

    .poker_card.slot-6 {
        bottom: 25px;
        right: 250px;
    }

    .poker_card.slot-1.desk {
        bottom: calc(100% - 60px);
        left: 50%;
        transform: rotate(90deg) translate(50%, 50%);
    }

    .poker_card.slot-2.desk {
        bottom: calc(100% - 60px);
        right: 50%;
        transform: rotate(90deg) translate(50%, -50%);
    }

    .poker_card.slot-3.desk {
        top: 50px;
        transform: translate(-50%, -50%) rotate(90deg);
        left: 50%;
    }

    .poker_card.slot-4.desk {
        top: 50px;
        right: 50%;
        transform: translate(50%, -50%) rotate(90deg);
    }

    .poker_card.slot-5.desk {
        transform: rotate(-90deg) translate(-50%, -50%);
        bottom: calc(100% - 50px);
        left: 50%;
    }

    .poker_card.slot-6.desk {
        transform: rotate(-90deg) translate(-50%, 50%);
        bottom: calc(100% - 50px);
        right: 50%;
    }

    .poker_card.slot.left-card {
        transform: translateX(50%);
    }

    .poker_card.slot.right-card {
        transform: translateX(-50%);
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
<div class="container text-center" id="tab_table" style="display: none;">
    <h3 id="table_name">Nombre de la Mesa</h3>
    <p class="text-muted" id="table_description">Esperando Jugadores</p>
    <div class="d-flex justify-content-center">
        <div class="poker_table w-100">
            <div class="poker_card slot slot-1 desk">
                <img src="/img/card.png" class="back left-card w-100"> <img src="/img/card.png" class="back right-card w-100">
            </div>
            <div class="poker_card slot slot-2 desk">
                <img src="/img/card.png" class="back left-card w-100"> <img src="/img/card.png" class="back right-card w-100">
            </div>
            <div class="poker_card slot slot-3 desk">
                <img src="/img/card.png" class="back left-card w-100"> <img src="/img/card.png" class="back right-card w-100">
            </div>
            <div class="poker_card slot slot-4 desk">
                <img src="/img/card.png" class="back left-card w-100"> <img src="/img/card.png" class="back right-card w-100">
            </div>
            <div class="poker_card slot slot-5 desk">
                <img src="/img/card.png" class="back left-card w-100"> <img src="/img/card.png" class="back right-card w-100">
            </div>
            <div class="poker_card slot slot-6 desk">
                <img src="/img/card.png" class="back left-card w-100"> <img src="/img/card.png" class="back right-card w-100">
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
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
                        if (response.success)
                            this.socket.send(JSON.stringify({
                                request: "poker.join_table",
                                data: {
                                    table: "{{$table}}"
                                }
                            }));
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
                        $("#tab_loading").fadeOut(500);
                        setTimeout(() => {
                            $("#tab_table").fadeIn(500);
                        }, 500);
                        break;
                    case "poker.table_status":
                        $("#table_name").html(response.response.name);
                        $("#table_description").html(response.response.description);
                        break;
                    case "poker.deal_cards":
                        $(`.slot-${response.response}`).removeClass("desk");
                        break;
                }
            });

            this.socket.addEventListener('close', () => {
                $("#lost_connection_alert_title").html("Reintentando conexión");
                $("#lost_connection_alert_description").html("Se perdió la conexión por unos segundos, reintentando conexión.");
                $("#poker_tables").html('');
                $("#lost_connection_alert").fadeIn(500);
                this.reconnect();
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
</script>
@endsection