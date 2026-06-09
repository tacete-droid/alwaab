import 'package:flutter/foundation.dart';

import 'auth_repository.dart';

class AuthState extends ChangeNotifier {
  AuthState(this._repo);

  final AuthRepository _repo;

  AuthUser? user;
  bool loading = true;
  String? error;

  bool get isLoggedIn => user != null;
  bool get canCreateVisit => user?.can('visits.create') ?? false;
  bool get canManageVisits => user?.can('visits.manage') ?? false;

  Future<void> bootstrap() async {
    loading = true;
    notifyListeners();
    user = await _repo.me();
    loading = false;
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    error = null;
    loading = true;
    notifyListeners();
    try {
      user = await _repo.login(email, password);
      loading = false;
      notifyListeners();
      return true;
    } catch (e) {
      error = 'فشل تسجيل الدخول — تحقق من البيانات والاتصال';
      loading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await _repo.logout();
    user = null;
    notifyListeners();
  }
}
