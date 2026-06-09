import 'dart:io';
import 'dart:math';

import 'package:dio/dio.dart';
import 'package:path_provider/path_provider.dart';

import 'offline_queue.dart';

String newActionId() =>
    '${DateTime.now().millisecondsSinceEpoch}_${Random().nextInt(99999)}';

bool isConnectionError(DioException e) =>
    e.type == DioExceptionType.connectionError ||
    e.type == DioExceptionType.connectionTimeout ||
    e.type == DioExceptionType.sendTimeout ||
    e.type == DioExceptionType.receiveTimeout;

Future<String> persistPhoto(File source) async {
  final dir = await getApplicationDocumentsDirectory();
  final offlineDir = Directory('${dir.path}/offline_photos');
  if (!await offlineDir.exists()) await offlineDir.create(recursive: true);

  final dest = File('${offlineDir.path}/${newActionId()}.jpg');
  await source.copy(dest.path);
  return dest.path;
}
