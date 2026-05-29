import 'dart:convert';
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import 'api_service.dart';

class OfflineSyncService {
  static final OfflineSyncService _instance = OfflineSyncService._internal();
  factory OfflineSyncService() => _instance;
  OfflineSyncService._internal();

  late Database _db;
  final ApiService _apiService = ApiService();

  Future<void> initialize() async {
    final databasesPath = await getDatabasesPath();
    final path = join(databasesPath, 'offline_sync.db');

    _db = await openDatabase(
      path,
      version: 1,
      onCreate: (db, version) async {
        await db.execute('''
          CREATE TABLE sync_queue (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            endpoint TEXT NOT NULL,
            method TEXT NOT NULL,
            payload TEXT NOT NULL,
            created_at INTEGER NOT NULL
          )
        ''');
      },
    );
  }

  Future<void> enqueue(String endpoint, String method, Map<String, dynamic> payload) async {
    await _db.insert('sync_queue', {
      'endpoint': endpoint,
      'method': method,
      'payload': jsonEncode(payload),
      'created_at': DateTime.now().millisecondsSinceEpoch,
    });
  }

  Future<int> getPendingCount() async {
    final result = await _db.rawQuery('SELECT COUNT(*) as count FROM sync_queue');
    return Sqflite.firstIntValue(result) ?? 0;
  }

  Future<void> syncPendingQueue() async {
    final List<Map<String, dynamic>> queue = await _db.query('sync_queue', orderBy: 'id ASC');
    
    for (var item in queue) {
      final int id = item['id'];
      final String endpoint = item['endpoint'];
      final String payloadStr = item['payload'];
      final Map<String, dynamic> payload = jsonDecode(payloadStr);

      try {
        final response = await _apiService.post(endpoint, payload);
        if (response.statusCode == 200 || response.statusCode == 201) {
          // Success, remove from queue
          await _db.delete('sync_queue', where: 'id = ?', whereArgs: [id]);
        }
      } catch (e) {
        // Network offline or server error, stop execution to preserve order
        break;
      }
    }
  }
}
