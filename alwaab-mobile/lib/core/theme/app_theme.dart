import 'package:flutter/material.dart';

class AppTheme {
  static const Color bg = Color(0xFF0A0F1E);
  static const Color surface = Color(0xFF1A2540);
  static const Color primary = Color(0xFF00D4FF);
  static const Color accent = Color(0xFFF6AD55);
  static const Color success = Color(0xFF22C55E);

  static ThemeData dark() {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      scaffoldBackgroundColor: bg,
      colorScheme: const ColorScheme.dark(
        primary: primary,
        secondary: accent,
        surface: surface,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: Color(0xFF0F172A),
        foregroundColor: primary,
        centerTitle: true,
      ),
      cardTheme: CardTheme(
        color: surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: const Color(0xFF0F172A),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
      ),
      floatingActionButtonTheme: const FloatingActionButtonThemeData(
        backgroundColor: primary,
        foregroundColor: bg,
      ),
    );
  }
}
