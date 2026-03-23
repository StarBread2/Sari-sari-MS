PRODUCTS 
    # actual products details
    products ->
        table name: products
            id (bigint) primary key auto_increment
            product_code (varchar 50) unique                            # Serial Number
            product_name (varchar 150)                                  # 1.5l Coca Cola
            category_id (bigint) nullable foreign key → categories.id   # Beverages 
            unit (varchar 50)                                           # bottle, pack, pcs
            created_at (timestamp)                      
            updated_at (timestamp)   

            /*
                # how would u know its current selling price? 
                SELECT selling_price
                FROM product_prices
                WHERE product_id = ?
                ORDER BY effective_from DESC
                LIMIT 1;
            */


    # categories for products (products -> categories)
        table name: categories
            id (bigint) primary key auto_increment
            category_name (varchar 100) unique



    # products references here for the prices
        # List of all past and current product prices 
            table name: product_prices
                id (bigint) primary key auto_increment
                product_id (bigint) foreign key → products.id

                selling_price (decimal 10,2)
                effective_to  (datetime)                           # much better way   

                created_at (timestamp)

                

            /*
                UNIQUE(product_id, effective_to )


                # also add here like what store they come from so that the owner can base what store sell the cheapest
                SELECT 
                    s.supplier_name,
                    pi.unit_cost,
                    p.product_name,
                    pu.purchase_date
                FROM purchase_items pi
                JOIN purchases pu ON pu.id = pi.purchase_id
                JOIN suppliers s ON s.id = pu.supplier_id
                JOIN products p ON p.id = pi.product_id
                WHERE pi.product_id = ?
                ORDER BY pi.unit_cost ASC;
            */







SUPPLIERS
    # actual tianges and suppliers
        table name: suppliers
            id (bigint) primary key auto_increment
            supplier_name (varchar 150)                 # Partosa (can also be a person = "Tig deliber og pan")
            contact_person (varchar 100) nullable       # Kaila sa partosa
            contact_number (varchar 20) nullable        # Number
            address (varchar 255) nullable              # Address
            created_at (timestamp)
            updated_at (timestamp)







OWNER STORE RESTOCK
    # every session the owner restockes (palit tiange)
        table name: purchases                                   # The whole kompra session
            id (bigint) primary key auto_increment              
            supplier_id (bigint) foreign key → suppliers.id      
            purchase_date (datetime)                             

            total_amount (decimal 12,2) nullable                # cache
            reference_no (varchar 100) nullable                 # resibo or someshit sa gipalit

            created_at (timestamp)
            updated_at (timestamp)


    # every product based on purchases (purchase_items -> purchases) (every product owner tiange session)
        table name: purchase_items                              # refrences purchases (pila ka items gi kompra, what item gi palit, tagpila pud)
            id (bigint) primary key auto_increment
            purchase_id (bigint) foreign key → purchases.id     # references asa na kuyog na pangompra
            product_id (bigint) foreign key → products.id       # what product gi kompra       [Problem: what if lain na ang price sa kompra?]

            quantity (int)                                      # Pila gipalit
            unit_cost (decimal 10,2)                            # Tag pila 
            selling_price (decimal 10,2) nullable               # Pila baligya (planning)

            created_at (timestamp)
            updated_at (timestamp)

            /*
                UNIQUE(purchase_id, product_id)
            */







STOCK MOVEMENTS
    # MAIN PLAYER OF COUNTING THE STOCKS
    # Recordes the flow of products in the store (both inflow(nangompra) and outflow(naay nipalit))
        table name: stock_movements
            id (bigint) primary key auto_increment

            product_id (bigint) foreign key → products.id

            movement_type (enum: 'PURCHASE','SALE','ADJUSTMENT')

            quantity (int)   # always positive
            unit_cost (decimal 10,2)   # cost at that moment

            remaining_quantity (int) # First In, First Out (https://chatgpt.com/s/t_69c0df6b0360819189ae63316c9c262a)

            purchase_item_id (bigint) nullable foreign key → purchase_items.id          #If someone purchases in the store (-stock)
            sale_item_id (bigint) nullable foreign key → sale_items.id                  #If owner purchases in the store (+stock)

            created_at (timestamp)

            /*
                DB CHECK (FOR FUTURE)
                IF movement_type = 'PURCHASE'
                    purchase_item_id NOT NULL
                    sale_item_id NULL

                IF movement_type = 'SALE'
                    sale_item_id NOT NULL
                    purchase_item_id NULL
            */






SALES
    # Naay nipalit
        table name: sales
            id (bigint) primary key auto_increment
            sale_date (datetime)
            total_amount (decimal 12,2)
            created_at (timestamp)

    # Unsay gipalit sa yawa
        table name: sale_items
            id (bigint) primary key auto_increment
            sale_id (bigint) foreign key → sales.id
            product_id (bigint) foreign key → products.id

            quantity (int)
            unit_price (decimal 10,2)
            unit_cost (decimal 10,2)   # VERY IMPORTANT

            created_at (timestamp)

            /*
                UNIQUE(sale_id, product_id)
            */
            







EXTRA (AUTOMATIC REORDER QUANTITY)
    # Settings if product reach minumum number then, predetermined reorder quantity by the owner madugang sa lista (paliton tiange)
        table name: product_reorder_levels
            id (bigint) primary key auto_increment
            product_id (bigint) foreign key → products.id

            min_stock (int)
            reorder_quantity (int)