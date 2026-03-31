<?php

namespace Database\Seeders;

use App\Models\Activite;
use App\Models\ActiviteMedia;
use App\Models\Avis;
use App\Models\Billet;
use App\Models\CampagnePublicitaire;
use App\Models\Categorie;
use App\Models\Commission;
use App\Models\Creneau;
use App\Models\EvenementStatistique;
use App\Models\Favori;
use App\Models\JournalNotification;
use App\Models\LigneReservation;
use App\Models\Litige;
use App\Models\Lieu;
use App\Models\MessageLitige;
use App\Models\Paiement;
use App\Models\PaiementPublicite;
use App\Models\Panier;
use App\Models\Prestataire;
use App\Models\PrestataireMembre;
use App\Models\Profil;
use App\Models\Promotion;
use App\Models\RegleCommission;
use App\Models\Remboursement;
use App\Models\Reservation;
use App\Models\SignalementAvis;
use App\Models\User;
use App\Models\Ville;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'Password123!';

        $admin = User::query()->create([
            'name' => 'Admin Principal',
            'email' => 'admin@allevent.local',
            'password' => $password,
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Profil::query()->create([
            'user_id' => $admin->id,
            'prenom' => 'Admin',
            'nom' => 'Principal',
            'telephone' => '+212600000001',
        ]);

        $clients = collect(range(1, 60))->map(function (int $i) use ($password) {
            $user = User::query()->create([
                'name' => "Client {$i}",
                'email' => "client{$i}@allevent.local",
                'password' => $password,
                'role' => 'client',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            Profil::query()->create([
                'user_id' => $user->id,
                'prenom' => "Client{$i}",
                'nom' => "Maroc{$i}",
                'telephone' => "+2126000001".str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            ]);

            return $user;
        });

        $prestataireUsers = collect(range(1, 6))->map(function (int $i) use ($password) {
            $user = User::query()->create([
                'name' => "Prestataire User {$i}",
                'email' => "prestataire{$i}@allevent.local",
                'password' => $password,
                'role' => 'prestataire',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            Profil::query()->create([
                'user_id' => $user->id,
                'prenom' => "Prestataire{$i}",
                'nom' => "Maroc{$i}",
                'telephone' => "+2126000002".str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            ]);

            return $user;
        });

        $categories = collect([
            'Loisirs & sorties',
            'Parcs & attractions',
            'Jeux & aventure',
            'Evenements & spectacles',
            'Sports & activites',
            'Ateliers creatifs',
            'Enfants & famille',
            'Vie nocturne',
        ])->map(fn (string $nom) => Categorie::query()->create([
            'nom' => $nom,
            'slug' => Str::slug($nom),
        ]));

        $villes = collect(['Casablanca', 'Marrakech', 'Rabat', 'Fes', 'Tanger', 'Agadir'])->map(fn (string $nom) => Ville::query()->create([
            'nom' => $nom,
            'code_pays' => 'MA',
        ]));

        $coords = [
            'Casablanca' => [33.5731, -7.5898],
            'Marrakech' => [31.6295, -7.9811],
            'Rabat' => [34.0209, -6.8416],
            'Fes' => [34.0181, -5.0078],
            'Tanger' => [35.7595, -5.8340],
            'Agadir' => [30.4278, -9.5981],
        ];
        $lieux = $villes->flatMap(function (Ville $ville) use ($coords) {
            $base = $coords[$ville->nom] ?? [33.9, -6.8];
            return collect(range(1, 2))->map(function (int $n) use ($ville, $base) {
                return Lieu::query()->create([
                    'ville_id' => $ville->id,
                    'nom' => "Lieu {$ville->nom} {$n}",
                    'adresse' => "Quartier central {$n}, {$ville->nom}",
                    'latitude' => $base[0] + ($n * 0.01),
                    'longitude' => $base[1] + ($n * 0.01),
                ]);
            });
        })->values();

        $prestataires = $prestataireUsers->map(function (User $user, int $i) {
            $prestataire = Prestataire::query()->create([
                'nom' => ['Atlas Experiences', 'Medina Tours', 'Sahara & Co', 'Riad Activities', 'Ocean Vibes', 'Fes Culture Hub'][$i] ?? ('Prestataire '.($i + 1)),
                'raison_sociale' => "Allevent Maroc Services ".($i + 1),
                'numero_fiscal' => 'MRC'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'statut' => 'valide',
                'valide_le' => now()->subDays(10 - $i),
            ]);

            PrestataireMembre::query()->create([
                'user_id' => $user->id,
                'prestataire_id' => $prestataire->id,
                'role_membre' => 'owner',
                'rejoint_le' => now()->subDays(12 - $i),
            ]);

            RegleCommission::query()->create([
                'prestataire_id' => $prestataire->id,
                'taux_pourcent' => 12.5,
                'debut_effet' => now()->subMonth()->toDateString(),
            ]);

            return $prestataire;
        });

        $templates = [
            ['titre' => 'Escape game medina', 'categorie' => 'Jeux & aventure', 'prix' => 220],
            ['titre' => 'Laser game arena', 'categorie' => 'Jeux & aventure', 'prix' => 190],
            ['titre' => 'Session paintball outdoor', 'categorie' => 'Jeux & aventure', 'prix' => 260],
            ['titre' => 'Bowling night challenge', 'categorie' => 'Loisirs & sorties', 'prix' => 140],
            ['titre' => 'Karting urbain premium', 'categorie' => 'Sports & activites', 'prix' => 280],
            ['titre' => 'Trampoline park freestyle', 'categorie' => 'Parcs & attractions', 'prix' => 130],
            ['titre' => 'Parc aquatique family pass', 'categorie' => 'Parcs & attractions', 'prix' => 170],
            ['titre' => 'Concert live gnawa', 'categorie' => 'Evenements & spectacles', 'prix' => 240],
            ['titre' => 'Stand-up comedy night', 'categorie' => 'Evenements & spectacles', 'prix' => 160],
            ['titre' => 'Cinema plein air experience', 'categorie' => 'Evenements & spectacles', 'prix' => 120],
            ['titre' => 'Atelier DJ debutant', 'categorie' => 'Ateliers creatifs', 'prix' => 200],
            ['titre' => 'Atelier theatre d impro', 'categorie' => 'Ateliers creatifs', 'prix' => 180],
            ['titre' => 'Kids club creatif weekend', 'categorie' => 'Enfants & famille', 'prix' => 110],
            ['titre' => 'Parcours aventure famille', 'categorie' => 'Enfants & famille', 'prix' => 150],
            ['titre' => 'Soiree rooftop DJ set', 'categorie' => 'Vie nocturne', 'prix' => 250],
        ];
        $categoriesByName = $categories->keyBy('nom');
        $activites = collect();
        foreach ($prestataires as $pIndex => $prestataire) {
            foreach ($templates as $tIndex => $template) {
                $ville = $villes[($tIndex + $pIndex) % $villes->count()];
                $categorie = $categoriesByName->get($template['categorie']) ?? $categories[0];
                $lieu = $lieux->firstWhere('ville_id', $ville->id);
                $prixBase = (int) $template['prix'] + ($pIndex * 30);
                $activite = Activite::query()->create([
                    'prestataire_id' => $prestataire->id,
                    'categorie_id' => $categorie->id,
                    'ville_id' => $ville->id,
                    'lieu_id' => $lieu?->id,
                    'titre' => $template['titre'],
                    'description' => "Experience {$template['titre']} au Maroc, organisee par {$prestataire->nom}.",
                    'statut' => 'publiee',
                    'prix_base' => $prixBase,
                ]);
                $activites->push($activite);

                ActiviteMedia::query()->create([
                    'activite_id' => $activite->id,
                    'url' => "https://picsum.photos/seed/maroc-{$prestataire->id}-{$tIndex}/900/600",
                    'ordre' => 0,
                ]);

                Creneau::query()->create([
                    'activite_id' => $activite->id,
                    'debut_at' => now()->addDays($tIndex + 1)->setHour(10),
                    'fin_at' => now()->addDays($tIndex + 1)->setHour(13),
                    'capacite_totale' => 24,
                    'capacite_restante' => 18,
                    'prix_applique' => $prixBase,
                    'statut' => 'ouvert',
                ]);
                Creneau::query()->create([
                    'activite_id' => $activite->id,
                    'debut_at' => now()->addDays($tIndex + 6)->setHour(16),
                    'fin_at' => now()->addDays($tIndex + 6)->setHour(19),
                    'capacite_totale' => 18,
                    'capacite_restante' => 14,
                    'prix_applique' => $prixBase + 20,
                    'statut' => 'ouvert',
                ]);
            }
        }

        $promotions = $prestataires->map(function (Prestataire $prestataire, int $i) {
            return Promotion::query()->create([
                'code' => 'PROMO'.($i + 1).'A',
                'libelle' => 'Promotion lancement '.($i + 1),
                'prestataire_id' => $prestataire->id,
                'type_remise' => 'pourcentage',
                'valeur' => 10 + $i,
                'montant_minimum_commande' => 10000,
                'utilisations_max' => 200,
                'utilisations_actuelles' => 3 + $i,
                'debut_at' => now()->subDays(5),
                'fin_at' => now()->addDays(30),
                'statut' => 'active',
            ]);
        });

        $campagnes = $prestataires->map(function (Prestataire $prestataire, int $i) use ($villes, $categories, $activites) {
            $campagne = CampagnePublicitaire::query()->create([
                'prestataire_id' => $prestataire->id,
                'titre' => 'Campagne premium '.($i + 1),
                'emplacement' => 'hero_home',
                'ville_id' => $villes[$i % $villes->count()]->id,
                'categorie_id' => $categories[$i % $categories->count()]->id,
                'activite_id' => $activites[$i]->id,
                'debut_at' => now()->subDays(2),
                'fin_at' => now()->addDays(20),
                'priorite' => 5 + $i,
                'budget_montant' => 12000 + ($i * 2500),
                'statut' => 'validee',
            ]);

            PaiementPublicite::query()->create([
                'campagne_publicitaire_id' => $campagne->id,
                'montant' => 4000 + ($i * 900),
                'devise' => 'MAD',
                'statut' => 'paye',
                'fournisseur' => 'simulation',
                'id_intention_fournisseur' => 'ad-intent-'.$campagne->id,
                'paye_le' => now()->subDay(),
            ]);

            return $campagne;
        });

        $reservations = collect();
        $paidPaymentIds = collect();
        foreach ($clients as $indexClient => $client) {
            for ($j = 0; $j < 3; $j++) {
                $activite = $activites[(($indexClient * 3) + $j) % $activites->count()];
                $creneau = Creneau::query()->where('activite_id', $activite->id)->firstOrFail();

                $panier = Panier::query()->create([
                    'user_id' => $client->id,
                    'statut' => 'converti',
                    'expire_le' => now()->addDays(2),
                ]);

                $reservation = Reservation::query()->create([
                    'user_id' => $client->id,
                    'panier_id' => $panier->id,
                    'promotion_id' => $promotions[$indexClient % $promotions->count()]->id,
                    'statut' => $j === 2 ? 'annulee' : 'payee',
                    'montant_total' => (float) $creneau->prix_applique * 2,
                    'montant_reduction' => 30,
                    'devise' => 'MAD',
                ]);
                $reservations->push($reservation);

                LigneReservation::query()->create([
                    'reservation_id' => $reservation->id,
                    'creneau_id' => $creneau->id,
                    'quantite' => 2,
                    'prix_unitaire_snapshot' => $creneau->prix_applique,
                ]);

                $paiement = Paiement::query()->create([
                    'reservation_id' => $reservation->id,
                    'montant' => $reservation->montant_total,
                    'devise' => 'MAD',
                    'statut' => $j === 2 ? 'annule' : 'paye',
                    'fournisseur' => 'simulation',
                    'id_intention_fournisseur' => 'pay-intent-'.$reservation->id,
                    'paye_le' => $j === 2 ? null : now()->subHours(3),
                ]);

                if ($j !== 2) {
                    $paidPaymentIds->push($paiement->id);

                    Billet::query()->create([
                        'reservation_id' => $reservation->id,
                        'code_public' => Str::upper(Str::random(10)),
                        'payload_qr' => json_encode(['reservation_id' => $reservation->id], JSON_THROW_ON_ERROR),
                        'statut' => 'emis',
                        'emis_le' => now()->subHours(2),
                    ]);
                }
            }
        }

        foreach ($paidPaymentIds as $paymentId) {
            $payment = Paiement::query()->findOrFail($paymentId);
            $reservation = Reservation::query()->findOrFail($payment->reservation_id);
            $firstLine = LigneReservation::query()->where('reservation_id', $reservation->id)->firstOrFail();
            $creneau = Creneau::query()->findOrFail($firstLine->creneau_id);
            $activite = Activite::query()->findOrFail($creneau->activite_id);

            Commission::query()->create([
                'paiement_id' => $payment->id,
                'prestataire_id' => $activite->prestataire_id,
                'montant_plateforme' => round((float) $payment->montant * 0.125, 2),
                'montant_net_prestataire' => round((float) $payment->montant * 0.875, 2),
                'devise' => 'MAD',
            ]);
        }

        $avis = collect();
        foreach ($clients as $idx => $client) {
            $reservationClient = $reservations->firstWhere('user_id', $client->id);
            if (! $reservationClient) {
                continue;
            }

            $ligne = LigneReservation::query()->where('reservation_id', $reservationClient->id)->first();
            if (! $ligne) {
                continue;
            }

            $creneau = Creneau::query()->find($ligne->creneau_id);
            if (! $creneau) {
                continue;
            }

            $review = Avis::query()->create([
                'user_id' => $client->id,
                'activite_id' => $creneau->activite_id,
                'reservation_id' => $reservationClient->id,
                'note' => 4 + ($idx % 2),
                'commentaire' => "Avis demo du client {$idx}.",
                'statut' => $idx === 2 ? 'en_attente_moderation' : 'visible',
                'reponse_prestataire' => $idx === 0 ? 'Merci pour votre retour.' : null,
                'repondu_le' => $idx === 0 ? now()->subHour() : null,
            ]);
            $avis->push($review);
        }

        if ($avis->count() >= 2) {
            Favori::query()->create([
                'user_id' => $clients[0]->id,
                'activite_id' => $avis[1]->activite_id,
            ]);
            Favori::query()->create([
                'user_id' => $clients[1]->id,
                'activite_id' => $avis[0]->activite_id,
            ]);

            SignalementAvis::query()->create([
                'avis_id' => $avis[1]->id,
                'user_id' => $clients[0]->id,
                'motif' => 'contenu_inapproprie',
                'details' => 'Signalement demo pour moderation.',
                'statut' => 'en_attente',
            ]);
        }

        $reservationLitige = $reservations->first();
        if ($reservationLitige) {
            $ligne = LigneReservation::query()->where('reservation_id', $reservationLitige->id)->first();
            if ($ligne) {
                $creneau = Creneau::query()->find($ligne->creneau_id);
                if ($creneau) {
                    $activite = Activite::query()->find($creneau->activite_id);
                    if ($activite) {
                        $litige = Litige::query()->create([
                            'reservation_id' => $reservationLitige->id,
                            'client_id' => $reservationLitige->user_id,
                            'prestataire_id' => $activite->prestataire_id,
                            'admin_id' => $admin->id,
                            'sujet' => 'Horaire non respecte',
                            'description' => 'Le client indique un decalage important de programme.',
                            'statut' => 'en_cours',
                            'priorite' => 'normale',
                        ]);

                        MessageLitige::query()->create([
                            'litige_id' => $litige->id,
                            'auteur_id' => $reservationLitige->user_id,
                            'message' => 'Bonjour, je souhaite ouvrir un litige.',
                            'interne_admin' => false,
                        ]);
                        MessageLitige::query()->create([
                            'litige_id' => $litige->id,
                            'auteur_id' => $admin->id,
                            'message' => 'Prise en charge par le support.',
                            'interne_admin' => true,
                        ]);
                    }
                }
            }
        }

        $reservationRemboursement = $reservations->firstWhere('statut', 'payee');
        if ($reservationRemboursement) {
            $paiement = Paiement::query()->where('reservation_id', $reservationRemboursement->id)->first();
            if ($paiement) {
                Remboursement::query()->create([
                    'paiement_id' => $paiement->id,
                    'reservation_id' => $reservationRemboursement->id,
                    'demandeur_id' => $reservationRemboursement->user_id,
                    'montant' => (float) $paiement->montant / 2,
                    'statut' => 'demande',
                    'motif' => 'Empêchement de dernière minute',
                ]);
            }
        }

        $usersForNotifications = $clients->concat($prestataireUsers)->push($admin);
        foreach ($usersForNotifications as $user) {
            JournalNotification::query()->create([
                'user_id' => $user->id,
                'canal' => 'email',
                'cle_modele' => 'notification_demo',
                'payload' => ['message' => 'Notification de test seed.'],
                'statut' => 'envoye',
                'envoye_le' => now()->subMinutes(15),
            ]);
        }

        EvenementStatistique::query()->create([
            'type_evenement' => 'reservation_payee',
            'user_id' => $clients[0]->id,
            'session_id' => Str::uuid()->toString(),
            'activite_id' => $activites[0]->id,
            'ville_id' => $activites[0]->ville_id,
            'prestataire_id' => $activites[0]->prestataire_id,
            'reservation_id' => $reservations[0]->id,
            'campagne_publicitaire_id' => $campagnes[0]->id,
            'payload' => ['source' => 'seed_demo'],
            'occurred_at' => now()->subMinutes(30),
        ]);

        foreach (range(1, 80) as $i) {
            $randomActivite = $activites[$i % $activites->count()];
            EvenementStatistique::query()->create([
                'type_evenement' => $i % 3 === 0 ? 'search_view' : ($i % 3 === 1 ? 'activity_view' : 'reservation_payee'),
                'user_id' => $clients[$i % $clients->count()]->id,
                'session_id' => Str::uuid()->toString(),
                'activite_id' => $randomActivite->id,
                'ville_id' => $randomActivite->ville_id,
                'prestataire_id' => $randomActivite->prestataire_id,
                'reservation_id' => $reservations[$i % $reservations->count()]->id,
                'campagne_publicitaire_id' => $campagnes[$i % $campagnes->count()]->id,
                'payload' => ['source' => 'seed_preprod', 'batch' => $i],
                'occurred_at' => now()->subMinutes(120 - $i),
            ]);
        }
    }
}
