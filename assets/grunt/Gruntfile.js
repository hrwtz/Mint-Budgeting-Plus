module.exports = function(grunt) {

	// All configuration goes here 
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		imagemin: {
		    dynamic: {
		        files: [{
		            expand: true,
		            cwd: '../img/src/',
		            src: ['**/*.{png,jpg,gif}'],
		            dest: '../img/pro/'
		        }]
		    }
		},

		compass: {
			dev: {
		    	options: {              
		        	sassDir: '../sass',
		        	cssDir: '../css',
		        	fontsDir: '../fonts',
		        	imagesDir: '../img/pro',
		        	images: '../img/pro',
		        	javascriptsDir: '../js/pro',
		        	environment: 'development',
		        	//outputStyle: 'nested',
		        	relativeAssets: false,
		        	httpPath: '.',
		        }
		    },
		},

		watch: {
		    images: {
		    	files: ['../img/src/**.{png,jpg,gif}'],
				tasks: ['imagemin'],
				options: {
					spawn: false,
				}
		    },
		    compass: {
		    	files: ['../**/*.{scss,sass}'],
		    	tasks: ['compass'],
		    },
		    svgstore: {
		    	files: ['../img/src/**.{svg}'],
				tasks: ['svgstore'],
		    },

		},

		svgstore: {
		    default: {
		    	files: {
					'../img/pro/svg-defs.svg': ['../img/src/svg/*.svg']
				}
		    }
		},


	});

	// Where we tell Grunt we plan to use this plug-in.
	grunt.loadNpmTasks('grunt-contrib-imagemin');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-compass');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-svgstore');

	// Where we tell Grunt what to do when we type "grunt" into the terminal.
	grunt.registerTask('build', ['compass']);
	grunt.registerTask('default', ['imagemin', 'svgstore', 'compass', 'watch']);

};
