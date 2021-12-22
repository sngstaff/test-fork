const STATUS_SUCCESS = 'success';
const STATUS_ERROR = 'error';
var app = new Vue({
    el: '#app',
    data: {
        login: '',
        pass: '',
        post: false,
        invalidLogin: false,
        invalidLoginMessage: 'Please write a username.',
        invalidPass: false,
        invalidSum: false,
        posts: [],
        addSum: 0,
        amount: 0,
        likes: 0,
        commentText: '',
        boosterpacks: [],
    },
    computed: {
        test: function () {
            var data = [];
            return data;
        }
    },
    created() {
        var self = this
        axios
            .get('/main_page/get_all_posts')
            .then(function (response) {
                self.posts = response.data.posts;
            })

        axios
            .get('/main_page/get_boosterpacks')
            .then(function (response) {
                self.boosterpacks = response.data.boosterpacks;
            })
    },
    methods: {
        logout: function () {
            console.log('logout');
        },
        logIn: function () {
            var self = this;
            if (self.login === '') {
                self.invalidLogin = true
            } else if (self.pass === '') {
                self.invalidLogin = false
                self.invalidPass = true
            } else {
                self.invalidLogin = false
                self.invalidPass = false

                form = new FormData();
                form.append("login", self.login);
                form.append("password", self.pass);

                axios.post('/main_page/login', form)
                    .then(function (response) {
                        if (response.data.user) {
                            location.reload();
                            setTimeout(function () {
                                $('#loginModal').modal('hide');
                            }, 500);
                        }
                        if (response.data.status == 'error') {
                            self.invalidLogin = true;
                            self.invalidLoginMessage = response.data.error_message;
                        }
                    })
            }
        },
        addComment: function (id) {
            var self = this;
            if (self.commentText) {

                var comment = new FormData();
                comment.append('assign_id', id);
                comment.append('text', self.commentText);

                axios.post(
                    '/main_page/comment',
                    comment
                ).then(function () {

                });
            }

        },
        refill: function () {
            var self = this;
            if (self.addSum === 0) {
                self.invalidSum = true
            } else {
                self.invalidSum = false
                sum = new FormData();
                sum.append('sum', self.addSum);
                axios.post('/main_page/add_money', sum)
                    .then(function (response) {
                        setTimeout(function () {
                            $('#addModal').modal('hide');
                        }, 500);
                    })
            }
        },
        openPost: function (id) {
            var self = this;
            axios
                .get('/main_page/get_post/' + id)
                .then(function (response) {
                    self.post = response.data.post;
                    if (self.post) {
                        setTimeout(function () {
                            $('#postModal').modal('show');
                        }, 500);
                    }
                })
        },
        addLike: function (type, id) {
            var self = this;
            const url = '/main_page/like_' + type + '/' + id;
            axios
                .get(url)
                .then(function (response) {
                    if (response.data.status == 'error') {
                        return alert(response.data.error_message);
                    }
                    self.likes = response.data.likes;
                })

        },
        buyPack: function (id) {
            var self = this;
            var pack = new FormData();
            pack.append('id', id);
            axios.post('/main_page/buy_boosterpack', pack)
                .then(function (response) {
                    self.amount = response.data.amount

                    if (response.data.status == 'error') {
                        return alert(response.data.error_message);
                    }

                    if (self.amount !== 0) {
                        setTimeout(function () {
                            $('#amountModal').modal('show');
                        }, 500);
                    }
                })
        }
    }
});

