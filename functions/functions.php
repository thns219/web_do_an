<?php
// Utility functions for fetching menu items
if (!function_exists('refValues')) {
	function refValues(array &$arr){
		$refs = [];
		foreach ($arr as $key => $value) $refs[$key] = &$arr[$key];
		return $refs;
	}
}

// Normalize image path from DB
if (!function_exists('resolveImagePath')) {
	/**
	 * Resolve image path stored in DB to a usable URL.
	 * - If path starts with 'http' or '/', return as-is.
	 * - If empty, return provided default.
	 * - Otherwise prefix with '/assets/img/'.
	 */
	function resolveImagePath($path, $default = '/assets/img/default-food.jpg') {
		$p = trim((string)$path);
		if ($p === '') return $default;
		// full URL
		if (stripos($p, 'http://') === 0 || stripos($p, 'https://') === 0) {
			return $p;
		}
		// root-relative path
		if (strpos($p, '/') === 0) return $p;
		// if path already points to known folders, make it root-relative
		$knownPrefixes = ['assets/', 'uploads/', 'images/'];
		foreach ($knownPrefixes as $pre) {
			if (stripos($p, $pre) === 0) return '/' . $p;
		}
		// otherwise assume filename stored, prefix with assets/img
		return '/assets/img/' . ltrim($p, '/');
	}
}

if (!function_exists('getMenuItems')) {
	/**
	 * Get menu items with optional search, limit and offset
	 * @param mysqli $conn
	 * @param array $opts ['keyword'=>'', 'category'=>'', 'limit'=>12, 'offset'=>0]
	 * @return array
	 */
	function getMenuItems($conn, $opts = []) {
		$keyword = $opts['keyword'] ?? '';
		$category = $opts['category'] ?? '';
		$limit = isset($opts['limit']) ? (int)$opts['limit'] : 12;
		$offset = isset($opts['offset']) ? (int)$opts['offset'] : 0;

		$where = " WHERE 1=1 ";
		$types = "";
		$params = [];

		if ($keyword !== "") {
			$where .= " AND (Tenmon LIKE ? OR Noidung LIKE ?) ";
			$like = "%{$keyword}%";
			$params[] = $like;
			$params[] = $like;
			$types .= "ss";
		}
		if ($category !== "") {
			$where .= " AND category = ? ";
			$params[] = $category;
			$types .= "s";
		}

		$sql = "SELECT Mamon, Tenmon, Giaban, Anh, Noidung FROM Monan {$where} ORDER BY Mamon DESC LIMIT ? OFFSET ?";
		$types .= "ii";
		$params[] = $limit;
		$params[] = $offset;

		$stmt = $conn->prepare($sql);
		if ($stmt === false) return [];

		$bindParams = array_merge([$types], $params);
		call_user_func_array([$stmt, 'bind_param'], refValues($bindParams));
		$stmt->execute();
		$res = $stmt->get_result();
		$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
		$stmt->close();
		return $rows;
	}
}

if (!function_exists('countMenuItems')) {
	function countMenuItems($conn, $keyword = '', $category = '') {
		$where = " WHERE 1=1 ";
		$types = "";
		$params = [];
		if ($keyword !== "") {
			$where .= " AND (Tenmon LIKE ? OR Noidung LIKE ?) ";
			$like = "%{$keyword}%";
			$params[] = $like; $params[] = $like;
			$types .= "ss";
		}
		if ($category !== "") {
			$where .= " AND category = ? ";
			$params[] = $category; $types .= "s";
		}
		$sql = "SELECT COUNT(*) AS cnt FROM Monan {$where}";
		$stmt = $conn->prepare($sql);
		if ($stmt === false) return 0;
		if (!empty($params)) {
			$bind = array_merge([$types], $params);
			call_user_func_array([$stmt, 'bind_param'], refValues($bind));
		}
		$stmt->execute();
		$r = $stmt->get_result();
		$row = $r ? $r->fetch_assoc() : null;
		$stmt->close();
		return $row ? (int)$row['cnt'] : 0;
	}
}

?>