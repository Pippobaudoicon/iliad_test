# **Laravel Project for Technical Test

## **1. Requisiti**
Prima di iniziare, assicurati di avere i seguenti strumenti installati:
- **Docker** e **Docker Compose**

---

## **2. Setup del Progetto**

### **2.1 Clona il repository**
```bash
git clone https://github.com/Pippobaudoicon/iliad_test.git
cd iliad_test
```

### **2.2 Configurazione del file `.env`**
Rinomina il file `.env.example` in `.env` e aggiorna i seguenti parametri:
```env
APP_NAME=Laravel
APP_ENV=local
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=iliad_db
DB_USERNAME=root
DB_PASSWORD=root

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
```

---

## **3. Avvio dell'Ambiente con Docker**

### **3.1 Costruisci e avvia i container**
```bash
docker compose up --build
```

I seguenti servizi saranno avviati (assicurati di avere le seguenti porte aperte):
- **PHP + Laravel**: `http://localhost:8000`
- **MySQL**: `localhost:3306`
- **phpMyAdmin**: `http://localhost:8080`
- **Meilisearch**: `http://localhost:7700`

### **3.2 Esegui le Migration e il Seeder**
Credenziali db dev:
- usr: root
- pwd: root

Creare la tabella se non viene creata da sola prima della popolazione
```bash
docker compose exec db mysql -u root -p -e "CREATE DATABASE iliad_db;"
```
Per creare le tabelle e popolare il database con dati fittizi (da rieseguire se eseguito php artisan test):
```bash
docker compose exec app php artisan migrate --seed
```

---

## **4. Gestione dello Stock**
### **Logica di Gestione**
La gestione dello stock avviene automaticamente durante le operazioni di creazione, modifica e cancellazione degli ordini:
- **Creazione di un ordine (`POST /api/orders`)**: Lo stock dei prodotti viene decrementato in base alla quantità richiesta.  
- **Modifica di un ordine (`PUT /api/orders/{id}`)**: Lo stock viene ricalcolato confrontando le differenze tra l’ordine precedente e quello nuovo.  
- **Eliminazione di un ordine (`DELETE /api/orders/{id}`)**: Lo stock dei prodotti viene ripristinato.  

> Nota: Il sistema utilizza **locking pessimistico** (`lockForUpdate`) per evitare problemi di concorrenza quando più ordini accedono agli stessi prodotti contemporaneamente.

---

## **5. API Documentation**
### **5.1 Orders API on POSTMAN**
Chiedere link da condividere (versione viewer non disponibile)

### **5.2 Orders API**
- **GET /api/orders** – Recupera l'elenco degli ordini (supporta ricerca con Meilisearch)  
  Esempio: `GET /api/orders?query=John`  
- **GET /api/orders/{id}** – Recupera il dettaglio di un ordine  
- **POST /api/orders** – Crea un nuovo ordine  
  **Body JSON:**
  ```json
    {
        "customer_name": "Jane Doe",
        "description": "Order for office supplies",
        "products": [
        { "id": 1, "quantity": 2 },
        { "id": 3, "quantity": 1 }
        ]
    }
  ```
- **PUT /api/orders/{id}** – Modifica un ordine esistente (con la stessa struttura del create)  
- **DELETE /api/orders/{id}** – Elimina un ordine  

### **5.3 Products API**
- **GET /api/products** – Recupera l'elenco dei prodotti (supporta ricerca con Meilisearch)  
  Esempio: `GET /api/products?query=laptop`  
- **GET /api/products/{id}** – Recupera il dettaglio di un prodotto  
- **POST /api/products** – Crea un nuovo prodotto  
  **Body JSON:**
  ```json
    {
    "name": "Laptop",
    "description": "Powerful gaming laptop",
    "price": 998.50,
    "stock_level": 20
    }
  ```
- **PUT /api/products/{id}** – Modifica un prodotto esistente (con la stessa struttura del create)  
- **DELETE /api/products/{id}** – Elimina un prodotto  

---

## **6. Test Automatizzati**
Il progetto include **test automatizzati** per garantire la qualità del codice e prevenire bug.

### **6.1 Scrivere e Lanciare i Test**
I test sono implementati utilizzando **PHPUnit**. Puoi trovarli nella directory `tests/Unit`.  
Esempio di test per le API degli ordini:
**`tests/Unit/OrderTest.php`**
```php
public function test_can_create_order()
{
    $response = $this->postJson('/api/orders', [
        'customer_name' => 'John Doe',
        'description' => 'Order for laptops',
        'products' => [
            ['id' => 1, 'quantity' => 2],
            ['id' => 2, 'quantity' => 1]
        ]
    ]);

    $response->assertStatus(201);
}
```

### **6.2 Eseguire i Test**
Per eseguire i test:
```bash
0
```

---

## **7. Ricerca con Meilisearch**
Puoi utilizzare **Meilisearch** per una ricerca veloce e avanzata sia per gli **ordini** che per i **prodotti**.  
Esempio di ricerca per ordini:
```bash
GET /api/orders?search=<key-search>
```

```bash
GET /api/products?search=<key-search>
```

---

## **8. Aggiornamento dell'Indice Meilisearch in Background**
Se si vuole popolare gli indici una tantum eseguire il seguente comando:
```bash
docker compose exec app php artisan scout:import "App\Models\Product"
docker compose exec app php artisan scout:import "App\Models\Order"
```

L'indice di Meilisearch viene aggiornato automaticamente in background utilizzando **Laravel Jobs**.  
Assicurati di avviare il worker per processare i job in coda:

```bash
docker compose exec app php artisan queue:work
```

---

## **9. Troubleshooting**
- **Errore di connessione al database?**  
  Verifica che il servizio `db` sia in esecuzione:  
  ```bash
  docker compose ps
  ```

---

## **10. Conclusione**
Questo progetto implementa un backend Laravel completo con:
- CRUD per ordini e prodotti  
- Ricerca avanzata con **Meilisearch**  
- **Sistema di code** per l'aggiornamento degli indici di ricerca
- **Gestione dello stock** con locking pessimistico per evitare problemi di concorrenza  
- **Test automatizzati** per garantire la qualità del codice e per avere sicurezza di un buon funzionamento in produzione