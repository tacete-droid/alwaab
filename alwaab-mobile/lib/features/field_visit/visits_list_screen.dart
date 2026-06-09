import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_theme.dart';
import 'visit_repository.dart';

class VisitsListScreen extends StatefulWidget {
  const VisitsListScreen({super.key});

  @override
  State<VisitsListScreen> createState() => _VisitsListScreenState();
}

class _VisitsListScreenState extends State<VisitsListScreen> {
  List<FieldVisitModel> _visits = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final list = await context.read<VisitRepository>().history();
      setState(() {
        _visits = list;
        _loading = false;
      });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Center(child: CircularProgressIndicator());

    return RefreshIndicator(
      onRefresh: _load,
      child: _visits.isEmpty
          ? ListView(children: const [SizedBox(height: 120), Center(child: Text('لا توجد زيارات'))])
          : ListView.builder(
              padding: const EdgeInsets.all(12),
              itemCount: _visits.length,
              itemBuilder: (_, i) {
                final v = _visits[i];
                return Card(
                  child: ListTile(
                    leading: Icon(
                      v.isActive ? Icons.gps_fixed : Icons.check_circle,
                      color: v.isActive ? AppTheme.primary : AppTheme.success,
                    ),
                    title: Text(v.projectName ?? '—'),
                    subtitle: Text(
                      v.lat != null ? '${v.lat!.toStringAsFixed(4)}, ${v.lng!.toStringAsFixed(4)}' : 'بدون GPS',
                      style: const TextStyle(fontSize: 12),
                    ),
                    trailing: Chip(
                      label: Text(v.status, style: const TextStyle(fontSize: 10)),
                      backgroundColor: v.isActive ? AppTheme.primary.withValues(alpha: 0.2) : null,
                    ),
                  ),
                );
              },
            ),
    );
  }
}
