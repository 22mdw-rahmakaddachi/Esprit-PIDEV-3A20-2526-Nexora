/*package com.pi;

import com.pi.entities.activite;
import com.pi.entities.Candidatureactivite;

import com.pi.entities.user;
//import com.pi.entity.ActiviteService;

import com.pi.entity.CandidatureService;
import com.pi.entity.ChatService;
import com.pi.entity.userservice;
import com.pi.utils.mydatabase;

import java.sql.SQLException;
import java.time.LocalDateTime;

public class Main {
    public static void main(String[] args) {
        userservice user = new userservice();
        int activiteIdToUpdate = 1;
        //ActiviteService activiteService = new ActiviteService();
        CandidatureService candidatureService = new CandidatureService();
        ChatService chatService = new ChatService();
        try {
           // user.ajouter(new user("Douiri","anouerdouiri7050@gmail.com","Anouer"));
           // user.supprimer(1);
            System.out.println(user.afficher());
        } catch (SQLException ex) {
            System.out.println(ex.getMessage());
        }

        try {
            // 1) AJOUT ACTIVITE (Create)
            // =========================
            /*activite act = new activite();
            act.setTitre("Camping Ain Draham");
            act.setType("Camping");
            act.setGenreCible("MIXTE"); // MASCULIN / FEMININ / MIXTE
            act.setLieu("Ain Draham");
            act.setDateActivite(LocalDateTime.of(2026, 2, 20, 8, 0));
            act.setDescription("Weekend camping, apportez votre tente");
            act.setCreateurId(1); // ⚠️ doit exister dans users

            activiteService.ajouter(act);
            System.out.println("✅ Activité ajoutée !");*/
            // =========================
            // 2) AFFICHER ACTIVITES (Read)
            // =========================
            //System.out.println("\n=== 📌 Liste des activités ===");
            /*for (activite a : activiteService.afficher()) {
                System.out.println(a);
            }*/
            // =========================
            // 3) MODIFIER ACTIVITE (Update)
            // =========================
            // ⚠️ ton icrud impose modifier(int id) => on utilise setToUpdate() avant
            /*activite newValues = new activite();
            newValues.setTitre("Camping Ain Draham - Updated");
            newValues.setType("Camping");
            newValues.setGenreCible("MIXTE");
            newValues.setLieu("Ain Draham");
            newValues.setDateActivite(LocalDateTime.of(2026, 2, 21, 9, 0));
            newValues.setDescription("Mise à jour: départ 9h");

            int activiteIdToUpdate = 1; // 🔁 mets un id existant
            activiteService.setToUpdate(newValues);
            activiteService.modifier(activiteIdToUpdate);
            System.out.println("\n✅ Activité modifiée !");*/
            // =========================
            // 4) POSTULER (Candidature)
            // =========================
            // user_id=2 doit exister
            /*Candidatureactivite cand = new Candidatureactivite(activiteIdToUpdate, 2,
                    "Je suis intéressé(e), je peux venir !");
            candidatureService.ajouter(cand);
            System.out.println("\n✅ Candidature envoyée !");

            System.out.println("\n=== 📌 Liste des candidatures ===");
            candidatureService.afficher().forEach(System.out::println);
            // =========================
            // 5) CHAT PRIVE (Conversation + Messages)
            // =========================*/
            /*int user1 = 1;
            int user2 = 2;

            int convId = chatService.getOrCreateConversation(user1, user2);
            System.out.println("\n✅ Conversation ID = " + convId);

            chatService.envoyerMessage(convId, user1, "Salut, tu viens au camping ?");
            chatService.envoyerMessage(convId, user2, "Oui, je confirme !");

            System.out.println("\n=== 💬 Messages de la conversation ===");
            chatService.lireMessages(convId).forEach(System.out::println);
            //6) SUPPRIMER (Delete) - option
            // =========================
             activiteService.supprimer(activiteIdToUpdate);

        }catch (Exception e) {
            System.out.println("❌ Erreur dans Main : " + e.getMessage());
            e.printStackTrace();
        }
}}*/