-- ============================================
-- INSERTION DES DONNÉES DE BASE
-- ============================================

-- 1. Insertion des opérateurs
INSERT INTO operateur (nom) VALUES ('YAS');
INSERT INTO operateur (nom) VALUES ('ORANGE');
INSERT INTO operateur (nom) VALUES ('AIRTEL');

-- 2. Insertion des préfixes par opérateur
-- YAS (id=1)
INSERT INTO operateur_prefixe (operateur_id, prefixe) VALUES (1, '034');
INSERT INTO operateur_prefixe (operateur_id, prefixe) VALUES (1, '038');

-- ORANGE (id=2)
INSERT INTO operateur_prefixe (operateur_id, prefixe) VALUES (2, '032');
INSERT INTO operateur_prefixe (operateur_id, prefixe) VALUES (2, '037');

-- AIRTEL (id=3)
INSERT INTO operateur_prefixe (operateur_id, prefixe) VALUES (3, '033');
INSERT INTO operateur_prefixe (operateur_id, prefixe) VALUES (3, '036');

-- 3. Insertion des types de transaction
INSERT INTO transaction_type (code, label) VALUES ('DEPOSIT', 'Dépôt');
INSERT INTO transaction_type (code, label) VALUES ('WITHDRAWAL', 'Retrait');
INSERT INTO transaction_type (code, label) VALUES ('TRANSFER', 'Transfert');

-- 4. Insertion des barèmes de frais (en Ariary)
-- Pour DÉPÔT (transaction_type_id = 1)
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (1, 100, 1000, 50);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (1, 1001, 5000, 50);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (1, 5001, 10000, 100);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (1, 10001, 25000, 200);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (1, 25001, 50000, 400);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (1, 50001, 100000, 800);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (1, 100001, 250000, 1500);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (1, 250001, 500000, 1500);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (1, 500001, 1000000, 2500);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (1, 1000001, 2000000, 3000);

-- Pour RETRAIT (transaction_type_id = 2) - mêmes frais que le dépôt
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (2, 100, 1000, 50);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (2, 1001, 5000, 50);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (2, 5001, 10000, 100);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (2, 10001, 25000, 200);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (2, 25001, 50000, 400);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (2, 50001, 100000, 800);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (2, 100001, 250000, 1500);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (2, 250001, 500000, 1500);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (2, 500001, 1000000, 2500);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (2, 1000001, 2000000, 3000);

-- Pour TRANSFERT (transaction_type_id = 3) - frais légèrement différents
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (3, 100, 1000, 100);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (3, 1001, 5000, 150);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (3, 5001, 10000, 200);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (3, 10001, 25000, 300);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (3, 25001, 50000, 500);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (3, 50001, 100000, 900);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (3, 100001, 250000, 1800);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (3, 250001, 500000, 2000);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (3, 500001, 1000000, 3000);
INSERT INTO montant (transaction_type_id, min_montant, max_montant, frais_montant) VALUES (3, 1000001, 2000000, 4000);

-- 5. Insertion des clients YAS (034)
INSERT INTO clients (numero, solde) VALUES ('0341000001', 250000);
INSERT INTO clients (numero, solde) VALUES ('0341000002', 150000);
INSERT INTO clients (numero, solde) VALUES ('0341000003', 50000);

-- Clients YAS (038)
INSERT INTO clients (numero, solde) VALUES ('0381000001', 1000000);
INSERT INTO clients (numero, solde) VALUES ('0381000002', 75000);
INSERT INTO clients (numero, solde) VALUES ('0381000003', 320000);

-- Clients ORANGE (032)
INSERT INTO clients (numero, solde) VALUES ('0321000001', 500000);
INSERT INTO clients (numero, solde) VALUES ('0321000002', 120000);
INSERT INTO clients (numero, solde) VALUES ('0321000003', 80000);

-- Clients ORANGE (037)
INSERT INTO clients (numero, solde) VALUES ('0371000001', 200000);
INSERT INTO clients (numero, solde) VALUES ('0371000002', 450000);
INSERT INTO clients (numero, solde) VALUES ('0371000003', 95000);

-- Clients AIRTEL (033)
INSERT INTO clients (numero, solde) VALUES ('0331000001', 600000);
INSERT INTO clients (numero, solde) VALUES ('0331000002', 180000);
INSERT INTO clients (numero, solde) VALUES ('0331000003', 72000);

-- Clients AIRTEL (036)
INSERT INTO clients (numero, solde) VALUES ('0361000001', 350000);
INSERT INTO clients (numero, solde) VALUES ('0361000002', 89000);
INSERT INTO clients (numero, solde) VALUES ('0361000003', 270000);

-- 6. Insertion des transactions (historique)
-- Génération de références uniques
-- Dépôts pour YAS
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (1, 1, 100000, 800, 99200, 'TXN-YAS-DEP-001', 'Reussi');
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (2, 1, 50000, 400, 49600, 'TXN-YAS-DEP-002', 'Reussi');
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (3, 1, 20000, 200, 19800, 'TXN-YAS-DEP-003', 'Reussi');

-- Retraits pour YAS
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (1, 2, 50000, 400, 49600, 'TXN-YAS-WTH-001', 'Reussi');
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (2, 2, 30000, 200, 29800, 'TXN-YAS-WTH-002', 'Reussi');

-- Dépôts pour ORANGE
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (7, 1, 200000, 1500, 198500, 'TXN-ORA-DEP-001', 'Reussi');
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (8, 1, 75000, 800, 74200, 'TXN-ORA-DEP-002', 'Reussi');
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (9, 1, 15000, 200, 14800, 'TXN-ORA-DEP-003', 'Reussi');

-- Transferts pour ORANGE
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (7, 3, 30000, 300, 29700, 'TXN-ORA-TRF-001', 'Reussi');
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (8, 3, 15000, 200, 14800, 'TXN-ORA-TRF-002', 'Reussi');

-- Dépôts pour AIRTEL
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (13, 1, 150000, 1500, 148500, 'TXN-AIR-DEP-001', 'Reussi');
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (14, 1, 35000, 200, 34800, 'TXN-AIR-DEP-002', 'Reussi');
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (15, 1, 25000, 200, 24800, 'TXN-AIR-DEP-003', 'Reussi');

-- Retraits pour AIRTEL
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (13, 2, 80000, 800, 79200, 'TXN-AIR-WTH-001', 'Reussi');
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (14, 2, 20000, 200, 19800, 'TXN-AIR-WTH-002', 'Reussi');

-- Transferts entre opérateurs
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (1, 3, 25000, 200, 24800, 'TXN-YAS-TO-ORA-001', 'Reussi');
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (7, 3, 45000, 500, 44500, 'TXN-ORA-TO-AIR-001', 'Reussi');
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (13, 3, 60000, 500, 59500, 'TXN-AIR-TO-YAS-001', 'Reussi');

-- Transaction en attente
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (2, 3, 30000, 300, 29700, 'TXN-YAS-TO-AIR-002', 'En attente');

-- Transaction échouée
INSERT INTO transactions (client_id, transaction_type_id, montant, frais_applique, montant_net, reference, status) 
VALUES (8, 2, 50000, 400, 49600, 'TXN-ORA-WTH-003', 'Echoue');