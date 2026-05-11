package com.pi.utils;

import com.pi.entities.user;

public class SessionManager {
    private static user currentUser;
    private static Integer currentPartenaireId;

    public static void setCurrentUser(user user) {
        currentUser = user;
    }

    public static user getCurrentUser() {
        return currentUser;
    }

    public static void setCurrentPartenaireId(int partenaireId) {
        currentPartenaireId = partenaireId;
    }

    public static Integer getCurrentPartenaireId() {
        return currentPartenaireId;
    }

    public static void clearSession() {
        currentUser = null;
        currentPartenaireId = null;
    }
}
