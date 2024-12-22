<template>
  <div class="admin-dashboard">
    <h2>管理者ダッシュボード</h2>
    <div class="admin-links">
      <router-link to="/admin/users" class="admin-link">ユーザー管理</router-link>
      <router-link to="/admin/reviews" class="admin-link">レビュー管理</router-link>
      <!-- 将来的に他の管理機能リンクを追加 -->
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminDashboard',
}
</script>

<style scoped>
.admin-dashboard {
  padding: 2em;
  text-align: center;
}

.admin-links {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 1em;
}

.admin-link {
  padding: 1em 2em;
  background-color: #6c757d;
  color: #fff;
  border-radius: 5px;
  text-decoration: none;
  font-weight: bold;
  transition: background-color 0.3s;
}

.admin-link:hover {
  background-color: #5a6268;
}
</style>
