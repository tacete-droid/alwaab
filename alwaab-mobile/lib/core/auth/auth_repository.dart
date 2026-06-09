import 'package:dio/dio.dart';

import '../api/api_client.dart';

class AuthUser {
  AuthUser({
    required this.id,
    required this.nameAr,
    required this.nameEn,
    required this.email,
    required this.permissions,
  });

  final String id;
  final String nameAr;
  final String nameEn;
  final String email;
  final List<String> permissions;

  factory AuthUser.fromJson(Map<String, dynamic> json) => AuthUser(
        id: json['id'] as String,
        nameAr: json['name_ar'] as String? ?? '',
        nameEn: json['name_en'] as String? ?? '',
        email: json['email'] as String? ?? '',
        permissions: (json['permissions'] as List<dynamic>?)
                ?.map((e) => e.toString())
                .toList() ??
            [],
      );

  String get displayName => nameAr.isNotEmpty ? nameAr : nameEn;

  bool can(String permission) => permissions.contains(permission);
}

class AuthRepository {
  AuthRepository(this._api);

  final ApiClient _api;

  Future<AuthUser> login(String email, String password) async {
    final response = await _api.dio.post('/auth/login', data: {
      'email': email,
      'password': password,
    });

    final data = response.data['data'] as Map<String, dynamic>;
    final token = data['token'] as String;
    await _api.setToken(token);

    return AuthUser.fromJson(data['user'] as Map<String, dynamic>);
  }

  Future<AuthUser?> me() async {
    final token = await _api.getToken();
    if (token == null) return null;

    try {
      final response = await _api.dio.get('/auth/me');
      return AuthUser.fromJson(response.data['data'] as Map<String, dynamic>);
    } on DioException {
      await _api.setToken(null);
      return null;
    }
  }

  Future<void> logout() async {
    try {
      await _api.dio.post('/auth/logout');
    } catch (_) {}
    await _api.setToken(null);
  }
}
