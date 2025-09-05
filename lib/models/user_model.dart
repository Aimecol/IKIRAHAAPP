class User {
  final int id;
  final String uuid;
  final String email;
  final String name;
  final String? phone;
  final UserRole role;
  final UserStatus status;
  final bool emailVerified;
  final String? profileImage;
  final DateTime createdAt;
  final DateTime updatedAt;

  User({
    required this.id,
    required this.uuid,
    required this.email,
    required this.name,
    this.phone,
    required this.role,
    required this.status,
    required this.emailVerified,
    this.profileImage,
    required this.createdAt,
    required this.updatedAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] ?? 0,
      uuid: json['uuid'] ?? '',
      email: json['email'] ?? '',
      name: json['name'] ?? '',
      phone: json['phone'],
      role: UserRole.fromString(json['role'] ?? 'client'),
      status: UserStatus.fromString(json['status'] ?? 'active'),
      emailVerified: json['email_verified'] == 1 || json['email_verified'] == true,
      profileImage: json['profile_image'],
      createdAt: DateTime.tryParse(json['created_at'] ?? '') ?? DateTime.now(),
      updatedAt: DateTime.tryParse(json['updated_at'] ?? '') ?? DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'uuid': uuid,
      'email': email,
      'name': name,
      'phone': phone,
      'role': role.value,
      'status': status.value,
      'email_verified': emailVerified,
      'profile_image': profileImage,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  User copyWith({
    int? id,
    String? uuid,
    String? email,
    String? name,
    String? phone,
    UserRole? role,
    UserStatus? status,
    bool? emailVerified,
    String? profileImage,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return User(
      id: id ?? this.id,
      uuid: uuid ?? this.uuid,
      email: email ?? this.email,
      name: name ?? this.name,
      phone: phone ?? this.phone,
      role: role ?? this.role,
      status: status ?? this.status,
      emailVerified: emailVerified ?? this.emailVerified,
      profileImage: profileImage ?? this.profileImage,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  @override
  String toString() {
    return 'User(id: $id, name: $name, email: $email, role: ${role.value})';
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is User && other.id == id && other.uuid == uuid;
  }

  @override
  int get hashCode => id.hashCode ^ uuid.hashCode;
}

enum UserRole {
  client('client', 'Client'),
  merchant('merchant', 'Merchant'),
  accountant('accountant', 'Accountant'),
  superAdmin('super_admin', 'Super Admin');

  const UserRole(this.value, this.displayName);

  final String value;
  final String displayName;

  static UserRole fromString(String value) {
    switch (value.toLowerCase()) {
      case 'client':
        return UserRole.client;
      case 'merchant':
        return UserRole.merchant;
      case 'accountant':
        return UserRole.accountant;
      case 'super_admin':
        return UserRole.superAdmin;
      default:
        return UserRole.client;
    }
  }

  bool get isClient => this == UserRole.client;
  bool get isMerchant => this == UserRole.merchant;
  bool get isAccountant => this == UserRole.accountant;
  bool get isSuperAdmin => this == UserRole.superAdmin;

  bool get canPlaceOrders => isClient;
  bool get canManageRestaurants => isMerchant || isSuperAdmin;
  bool get canViewFinancials => isAccountant || isSuperAdmin;
  bool get canManageUsers => isSuperAdmin;
}

enum UserStatus {
  active('active', 'Active'),
  inactive('inactive', 'Inactive'),
  suspended('suspended', 'Suspended');

  const UserStatus(this.value, this.displayName);

  final String value;
  final String displayName;

  static UserStatus fromString(String value) {
    switch (value.toLowerCase()) {
      case 'active':
        return UserStatus.active;
      case 'inactive':
        return UserStatus.inactive;
      case 'suspended':
        return UserStatus.suspended;
      default:
        return UserStatus.active;
    }
  }

  bool get isActive => this == UserStatus.active;
  bool get isInactive => this == UserStatus.inactive;
  bool get isSuspended => this == UserStatus.suspended;
}

class UserAddress {
  final int id;
  final int userId;
  final AddressType type;
  final String address;
  final String? phone;
  final bool isDefault;
  final DateTime createdAt;
  final DateTime updatedAt;

  UserAddress({
    required this.id,
    required this.userId,
    required this.type,
    required this.address,
    this.phone,
    required this.isDefault,
    required this.createdAt,
    required this.updatedAt,
  });

  factory UserAddress.fromJson(Map<String, dynamic> json) {
    return UserAddress(
      id: json['id'] ?? 0,
      userId: json['user_id'] ?? 0,
      type: AddressType.fromString(json['type'] ?? 'home'),
      address: json['address'] ?? '',
      phone: json['phone'],
      isDefault: json['is_default'] == 1 || json['is_default'] == true,
      createdAt: DateTime.tryParse(json['created_at'] ?? '') ?? DateTime.now(),
      updatedAt: DateTime.tryParse(json['updated_at'] ?? '') ?? DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'type': type.value,
      'address': address,
      'phone': phone,
      'is_default': isDefault,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
}

enum AddressType {
  home('home', 'Home'),
  work('work', 'Work'),
  other('other', 'Other');

  const AddressType(this.value, this.displayName);

  final String value;
  final String displayName;

  static AddressType fromString(String value) {
    switch (value.toLowerCase()) {
      case 'home':
        return AddressType.home;
      case 'work':
        return AddressType.work;
      case 'other':
        return AddressType.other;
      default:
        return AddressType.home;
    }
  }
}
