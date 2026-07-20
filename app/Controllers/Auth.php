<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function loginAuth()
    {
        $numero = $this->request->getPost('numero');

        $db = \Config\Database::connect();

        $user = $db->query("SELECT * FROM clients WHERE numero = '$numero'")->getRowArray();

        if ($user) {
            // L'utilisateur existe, vous pouvez effectuer d'autres vérifications si nécessaire
            // Par exemple, vérifier le mot de passe si vous l'avez stocké dans la base de données
            session()->set([
                    'user_id'   => $user['id'],
                    'user_numero' => $user['numero'],
                    'user_solde' => $user['solde'],
                ]);
            // Rediriger vers la page d'accueil ou une autre page après la connexion réussie
            return redirect()->to('/solde');
        } else {
            // L'utilisateur n'existe pas, afficher un message d'erreur ou rediriger vers la page de connexion
            return redirect()->to('/login')->with('error', 'Numéro d\'utilisateur invalide.');
        }
    }
}