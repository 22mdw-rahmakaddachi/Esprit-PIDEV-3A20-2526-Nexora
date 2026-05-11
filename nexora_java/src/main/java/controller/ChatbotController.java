package controller;

import com.pi.utils.ChatbotAPI;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.paint.Color;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;

public class ChatbotController {

    @FXML private VBox chatContainer;
    @FXML private ScrollPane scrollPane;
    @FXML private TextField messageField;
    @FXML private Button sendButton;

    private ChatbotAPI chatbotAPI;

    @FXML
    public void initialize() {
        chatbotAPI = new ChatbotAPI();
        
        // Auto-scroll vers le bas
        chatContainer.heightProperty().addListener((obs, oldVal, newVal) -> {
            scrollPane.setVvalue(1.0);
        });
        
        // Envoyer avec Enter
        messageField.setOnAction(e -> handleSendMessage());
        
        // Message de bienvenue
        addBotMessage(chatbotAPI.processMessage("bonjour"));
    }

    @FXML
    public void handleSendMessage() {
        String userMessage = messageField.getText().trim();
        
        if (userMessage.isEmpty()) {
            return;
        }
        
        // Afficher le message de l'utilisateur
        addUserMessage(userMessage);
        
        // Vider le champ
        messageField.clear();
        
        // Traiter le message et obtenir la réponse
        String botResponse = chatbotAPI.processMessage(userMessage);
        
        // Afficher la réponse du bot (avec un petit délai pour l'effet)
        new Thread(() -> {
            try {
                Thread.sleep(500); // Simuler le temps de réflexion
                javafx.application.Platform.runLater(() -> addBotMessage(botResponse));
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }).start();
    }

    private void addUserMessage(String message) {
        HBox messageBox = new HBox(10);
        messageBox.setAlignment(Pos.CENTER_RIGHT);
        messageBox.setPadding(new Insets(5, 10, 5, 50));
        
        VBox bubble = createMessageBubble(message, "#2980b9", "#ffffff", true);
        messageBox.getChildren().add(bubble);
        
        chatContainer.getChildren().add(messageBox);
    }

    private void addBotMessage(String message) {
        HBox messageBox = new HBox(10);
        messageBox.setAlignment(Pos.CENTER_LEFT);
        messageBox.setPadding(new Insets(5, 50, 5, 10));
        
        // Icône du bot
        Label icon = new Label("🤖");
        icon.setStyle("-fx-font-size: 24px;");
        
        VBox bubble = createMessageBubble(message, "#ecf0f1", "#2c3e50", false);
        
        messageBox.getChildren().addAll(icon, bubble);
        chatContainer.getChildren().add(messageBox);
    }

    private VBox createMessageBubble(String message, String bgColor, String textColor, boolean isUser) {
        VBox bubble = new VBox(5);
        bubble.setStyle(String.format(
            "-fx-background-color: %s; " +
            "-fx-background-radius: 15; " +
            "-fx-padding: 12; " +
            "-fx-max-width: 400;",
            bgColor
        ));
        
        Label messageLabel = new Label(message);
        messageLabel.setWrapText(true);
        messageLabel.setStyle(String.format(
            "-fx-text-fill: %s; " +
            "-fx-font-size: 13px;",
            textColor
        ));
        
        Label timeLabel = new Label(LocalTime.now().format(DateTimeFormatter.ofPattern("HH:mm")));
        timeLabel.setStyle(String.format(
            "-fx-text-fill: %s; " +
            "-fx-font-size: 10px; " +
            "-fx-opacity: 0.7;",
            textColor
        ));
        timeLabel.setAlignment(isUser ? Pos.CENTER_RIGHT : Pos.CENTER_LEFT);
        
        bubble.getChildren().addAll(messageLabel, timeLabel);
        return bubble;
    }

    @FXML
    public void handleClearChat() {
        chatContainer.getChildren().clear();
        addBotMessage("Chat effacé! Comment puis-je vous aider?");
    }
    
    @FXML
    public void handleSuggestion1() {
        messageField.setText("produits à moins de 50 TND");
        handleSendMessage();
    }
    
    @FXML
    public void handleSuggestion2() {
        messageField.setText("quelles sont les promotions?");
        handleSendMessage();
    }
    
    @FXML
    public void handleSuggestion3() {
        messageField.setText("montrez-moi des produits de camping");
        handleSendMessage();
    }
}
