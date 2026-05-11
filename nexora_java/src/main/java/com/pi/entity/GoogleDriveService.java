package com.pi.entity;

import com.google.api.client.http.FileContent;
import com.google.api.services.drive.Drive;
import com.google.api.services.drive.model.Permission;
import com.google.api.services.drive.model.File;
import com.google.auth.http.HttpCredentialsAdapter;
import com.google.auth.oauth2.GoogleCredentials;

import java.io.IOException;
import java.nio.file.Paths;
import java.security.GeneralSecurityException;
import java.util.Collections;

public class GoogleDriveService {

    private static final String CREDENTIALS_FILE_PATH = "src/main/resources/credentials.json"; // ton JSON compte de service

    public static Drive getDriveService() throws IOException, GeneralSecurityException {
        GoogleCredentials credentials = GoogleCredentials.fromStream(
                        java.nio.file.Files.newInputStream(Paths.get(CREDENTIALS_FILE_PATH)))
                .createScoped(Collections.singleton("https://www.googleapis.com/auth/drive.file"));

        return new Drive.Builder(
                com.google.api.client.googleapis.javanet.GoogleNetHttpTransport.newTrustedTransport(),
                com.google.api.client.json.gson.GsonFactory.getDefaultInstance(),
                new HttpCredentialsAdapter(credentials))
                .setApplicationName("Travel App")
                .build();
    }

    public static String uploadFile(java.io.File filePath) throws IOException, Exception {
        Drive service = getDriveService();

        // Metadata du fichier
        File fileMetadata = new File();
        fileMetadata.setName(filePath.getName());

        // ✅ Ici on définit le dossier cible
        fileMetadata.setParents(Collections.singletonList("1GG-jfhkEU7rrkFmP1RZnP-aKzlOKGDAR"));

        FileContent mediaContent = new FileContent("image/jpeg", filePath);

        File file = service.files()
                .create(fileMetadata, mediaContent)
                .setFields("id, webViewLink, webContentLink")
                .execute();

        // Rendre le fichier public
        Permission permission = new Permission();
        permission.setType("anyone");
        permission.setRole("reader");
        service.permissions().create(file.getId(), permission).execute();

        // Retourne l'URL partageable
        return "https://drive.google.com/uc?id=" + file.getId();
    }
}